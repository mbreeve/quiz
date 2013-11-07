//
// TreeViewer.js: views, and provides ui for, the javascript tree built
// by TreeBuilder
// Project: STS - Specialised Test Setter
//

function TreeViewer(parent)
{
	this.parent = parent;
	this.root = parent.root;
	this.launch = this.root.launch;
	this.setup = false;

	this.menu = new MainMenu(this.root, "treeViewer");
	this.menu.setFixed();
	this.menu.hide();

	this.root.$divForm.append(
		this.$divOuter = $("<div id='treeViewer'></div>").hide());
	var lister = this;
	this.$divOuter.on("click", ".selectable", function()
	{
		lister.selectRow($(this));
	});

	// Get the test editor, creating if necessary
	this.getTestEditor = function()
	{
		if (!this.testEditor)
		{
			this.testEditor = new TestEditor(this);
		}
		this.viewing = this.testEditor;
		return this.testEditor;
	};

	// Get the question editor, creating if necessary
	this.getQuestionEditor = function()
	{
		if (!this.questionEditor)
		{
			this.questionEditor = new QuestionEditor(this);
		}
		this.viewing = this.questionEditor;
		return this.questionEditor;
	};

	// This is the response to ajax calls, i.e. asynchronously to everything else.
	this.dispatch = function(data)
	{
		return true;
	}

	// Finish this and return to the calling (parent) browser/editor
	this.finish = function()
	{
		this.menu.hide();
		this.$divOuter.hide();
		return this;
	};

	// Resume state on this browser/editor after returning from finish() on a child
	this.resume = function()
	{
		this.$divOuter.show();
		this.menu.show();
		this.viewing = this;
		this.expandNodes(false);
		return this;
	};

	// Save state on this browser/editor before calling enter() on a child
	this.suspend = function()
	{
		this.root.whiteBoard.clearGreeting();
		this.$divOuter.hide();
		this.menu.hide();
		return this;
	};

	// Update the view of the tests.
	this.updateTree = function()
	{
		this.buildTree();
		this.selectCurRow();
		if (!this.viewing)
		{
			this.root.whiteBoard.setGreeting("Display All Tests");
			this.resume();
		}
		return this;
	}

	this.buildTree = function()
	{
		var $tbody, $row, ttidDefault;
		this.$divOuter.empty().append(
			this.$table = $("<table id='tableLister' class='stand'></table>").append(
				$("<thead></thead>").append(
					$("<tr></tr>").append(
						$("<th></th>").addClass("identifier").text("Name"),
						$("<th></th>").addClass("justDate").text("Date Added"),
						$("<th></th>").addClass("keywords").text("Keywords"))),
				$tbody = $("<tbody></tbody>")));

		var root = this.root;
		$.each(root.logged.idSetters, function()
		{
			var idSetter = this;
			var setter = root.setters[idSetter];
			var eidSetter = "S" + idSetter;
			$.each(setter.idTests, function()
			{
				var idTest = this;
				var test = root.tests[idTest];
				var eidTest = "T" + idTest;
				var isReal = idTest != 0;
				var $ident;
				$tbody.append(
					$row = $("<tr></tr>").addClass("selectable").append(
						$ident = $("<td></td>").addClass("identifier").text(idTest + ":" + test.fields.ident),
						$("<td></td>").addClass("justDate").text(test.fields.added),
						$("<td></td>").addClass("keywords").text(test.fields.kwText)));
				$row.data(
				{
					//pid: eidSetter;
					ttid: eidSetter + eidTest,
					setter: setter,
					idSetter: idSetter,					// this has to be separate
					test: test,
					table: "test",
					branch: isReal
				});
				if (!isReal)
				{
					$ident.text("Create Test");
					// This (first time through) is the default selected row (i.e. the "path") ...
					ttidDefault || (ttidDefault = eidSetter + eidTest);
					return true;			// c.f. continue (not break) in normal loop
				}
				$ident.prepend($("<span></span>").addClass("test"));
				if (test.$row && test.$row.hasClass("expanded"))
				{
					$row.addClass("expand");
				}
				// Keep the record reference against the row in a reasonably enduring way.
				root.tests[idTest].$row = $row;

				$.each(test.idQuestions, function()
				{
					var idQuestion = this;
					var question = root.questions[idQuestion];
					var eidQuestion = "Q" + idQuestion;
					var isReal = idQuestion != 0;
					var $ident;
					$tbody.append(
						$row = $("<tr></tr>").addClass("selectable").append(
							$ident = $("<td></td>").addClass("identifier").text(idQuestion + ":" + question.fields.ident),
							$("<td></td>").addClass("justDate"),
							$("<td></td>").addClass("keywords")));
					$row.data(
					{
						pid: eidSetter + eidTest,
						ttid: eidSetter + eidTest + eidQuestion,
						idTest: idTest,					// this has to be separate
						question: question,
						table: "question"
					});
					if (!isReal)
					{
						$ident.text("Create Question");
						return true;			// c.f. continue (not break) in normal loop
					}
					$ident.prepend($("<span></span>").addClass("question"));
					if (question.$row && question.$row.hasClass("expanded"))
					{
						$row.addClass("expand");
					}
					// Keep the record reference against the row in a reasonably enduring way.
					root.questions[idQuestion].$row = $row;
				});
			});
		});

		// Remember the default. Also, if no row has been selected, use the default ...
		this.ttidDefault = ttidDefault;
		this.ttidCurrent || (this.ttidCurrent = ttidDefault);
		
		// Mark each selectable HTML line with its id/path
		var $rows = $tbody.children();
		$rows.each(function()
		{
			var $this = $(this);
			$this.attr("id", $this.data("ttid"));
		});
		this.$expandeds = $rows.filter(".expand");
		this.$table.treetable(
		{
			branchAttr: "branch",
			expandable: true,
			nodeIdAttr: "ttid",
			parentIdAttr: "pid"
		});
		this.expandNodes(true);
	};

	this.selectCurRow = function()
	{
		// Find out which row to select. First time through, this will be the
		// zeroth row. Otherwise it will be the row with the test (identified
		// by ttid - its treetable id) that was last selected. After a deletion,
		// that row will no longer exist, so the row with the same position is
		// selected instead (taking care not not go off the end of the table).
		var $rows = this.$divOuter.find(".selectable");
		var maxRow = $rows.length - 1;
		if (maxRow >= 0)
		{
			var selector = "#" + this.ttidCurrent;
			var $row = $rows.filter(selector);
			if ($row.length == 0)
			{
				// If the currently selected row has disappeared, whittle the path down
				// until one is found.
				selector = "#" + this.ttidDefault;
				$row = $rows.filter(selector);
			}
			if ($row.length > 0)
			{
				this.selectRow($row.eq(0));
			}
		}
		return this;
	};

	this.selectRow = function($row)
	{
		$row.addClass("selected").siblings().removeClass("selected");
		this.ttidCurrent = $row.data("ttid");

		this.menu.clearItems();
		var thisObj = this;
		switch ($row.data("table"))
		{
		case "test":
			var setter = $row.data("setter");
			var test = $row.data("test");
			var ident = " <" + test.fields.ident + ">";
			if (parseInt(test.dbid))
			{
				this.menu.addItem("View/Edit" + ident, true, function()
				{
					thisObj.suspend().getTestEditor().edit(setter, test);
				});
				this.menu.addItem("Delete" + ident, true, function()
				{
					thisObj.suspend().getTestEditor().delete(setter, test);
				});
			}
			else
			{
				this.menu.addItem("Create New Test", true, function()
				{
					thisObj.suspend().getTestEditor().edit(setter, test);
				});
			}
			break;
		case "question":
			var idTest = $row.data("idTest");
			var question = $row.data("question");
			var ident = " <" + question.fields.ident + ">";
			if (parseInt(question.dbid))
			{			
				this.menu.addItem("Update" + ident, true, function()
				{
					thisObj.suspend().getQuestionEditor().update(question, idTest);
				});
				this.menu.addItem("Delete" + ident, true, function()
				{
					thisObj.suspend().getQuestionEditor().delete(question);
				});
			}
			else
			{
				this.menu.addItem("Create New Question", true, function()
				{
					thisObj.suspend().getQuestionEditor().create(question, idTest);
				});
			}
			break;
		}
		this.menu.addItem("Exit Test Management", true, function()
		{
			thisObj.parent.launch.exitTests();
		});
		return this;
	};

	this.expandNodes = function(required)
	{
		if (this.viewing == this)
		{
			if (required || this.outstanding)
			{
				var $table = this.$table;
				this.$expandeds.each(function()
				{
					$table.treetable("expandNode", $(this).data("ttid"));
				});
				this.outstanding = false;
			}
		}
		else
		{
			if (required)
			{
				this.outstanding = true;
			}
		}
	};
}
