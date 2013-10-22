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

	this.viewing = null;
	this.curIndex = 0;
	this.ttid = 0;
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

	// Get the questions displayer, creating if necessary ...
	this.getQuestionsLister = function()
	{
		if (!this.questionsLister)
		{
			this.questionsLister = new QuestionsLister(this);
		}
		return this.questionsLister;
	};

	// Get the test editor, creating if necessary
	this.getTestEditor = function()
	{
		if (!this.testEditor)
		{
			this.testEditor = new TestEditor(this);
		}
		return this.testEditor;
	};

	// This is the response to ajax calls, i.e. asynchronously to everything else.
	this.dispatch = function(data)
	{
		return this.getTestEditor().dispatch(data);
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
		this.root.whiteBoard.setGreeting("Display All Tests");
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
	this.updateTests = function()
	{
		this.displayAll();
		if (!this.viewing || this.viewing == this)
		{
			this.selectCurRow();
			this.resume();
		}
		return this;
	}

	this.displayAll = function()
	{
		var $table, $tbody, $row;

		this.$divOuter.empty().append(
			$table = $("<table id='tableLister' class='stand'></table>").append(
				$("<thead></thead>").append(
					$("<tr></tr>").append(
						$("<th></th>").addClass("itemName").text("Name"),
						$("<th></th>").addClass("testDate").text("Date Added"),
						$("<th></th>").addClass("keywords").text("Keywords"))),
				$tbody = $("<tbody></tbody>")));

		$.each(this.root.setters, function(idSetter, setter)
		{
			$tbody.append(
				$row = $("<tr></tr>").addClass("selectable").append(
					$("<td></td>").addClass("itemName").text("Create Test"),
					$("<td></td>").addClass("testDate"),
					$("<td></td>").addClass("keywords")));
			$row.data(
			{
				ttid: "T-Before",
				setter: setter,
				action: "newTest"
			});

			$.each(setter.tests, function(idTest, test)
			{
				$tbody.append(
					$row = $("<tr></tr>").addClass("selectable").append(
						$("<td></td>").addClass("itemName").text(test.fields.name).prepend(
							$("<span></span>").addClass("test")),
						$("<td></td>").addClass("testDate").text(test.fields.added),
						$("<td></td>").addClass("keywords").text(test.fields.kwText)));
				$row.data(
				{
					ttid: idTest,
					setter: setter,
					test: test,
					action: "test",
					branch: true
				});
				if (test.$row && test.$row.hasClass("expanded"))
				{
					$row.addClass("xxx");
				}
				// Keep the record reference against the row in a reasonably enduring way.
				setter.tests[idTest].$row = $row;

				$tbody.append(
					$row = $("<tr></tr>").addClass("selectable").append(
						$("<td></td>").addClass("itemName").text("Create Question"),
						$("<td></td>").addClass("testDate"),
						$("<td></td>").addClass("keywords")));
				$row.data(
				{
					pid: idTest,
					ttid: "Q-After",
					action: "newQuestion"
				});
/*
				case "question record":
					var question = test.db;
					$tbody.append(
						$row.append(
							$("<td></td>").addClass("questionName").text("question.name").prepend(
								$("<span></span>").addClass("question"))));
					break;
*/
			});
		});
		this.$expandeds = $tbody.children(".xxx");
		$table.treetable(
		{
			branchAttr: "branch",
			expandable: true,
			nodeIdAttr: "ttid",
			parentIdAttr: "pid"
		});
		this.$table = $table;
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
			var $row = $rows.filter("#" + this.ttid);
			if ($row.length == 0)
			{
				$row = $rows.eq(Math.min(Math.max(this.curIndex, 0), maxRow));
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
		this.$row = $row;
		this.curIndex = $row.index();
		var ttid = this.ttid = $row.data("ttid");
		var setter = { };
		var test = { };
		var create = false;
		switch ($row.data("action"))
		{
		case "newTest":
			create = true;
			setter = $row.data("setter");
			test.fields = makeTestFields({ });
			break;
		case "test":
			setter = $row.data("setter");
			test = $row.data("test");
			break;
		}

		this.menu.clearItems();
		var thisObj = this;
		var te = this.getTestEditor();
		if (test.fields)
		{
			this.getTestEditor().select(setter, test.fields);
			var name = " <" + test.fields.name + ">";
			if (create)
			{
				this.menu.addItem("Create New Test", true, function()
				{
					thisObj.viewing = te;
					thisObj.suspend().getTestEditor().enter();
				});
			}
			else
			{
				this.menu.addItem("View/Edit" + name, true, function()
				{
					thisObj.viewing = te;
					thisObj.suspend().getTestEditor().enter();
				});
				this.menu.addItem("Delete" + name, true, function()
				{
					thisObj.viewing = te;
					thisObj.suspend().getTestEditor().delete();
				});
			}
		}
		this.menu.addItem("Exit Test Management", true, function()
		{
			thisObj.parent.launch.exitTests();
		});

		this.menu.show();
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
