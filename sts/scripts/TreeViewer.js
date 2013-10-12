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
	this.curDbid = 0;
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

	this.displayAll = function()
	{
		var $tableTests;
		var $tbody;

		this.$divOuter.empty().append(
			$tableTests = $("<table id='tableLister' class='stand'></table>").append(
				$("<thead></thead>").append(
					$("<tr></tr>").append(
						$("<th></th>").addClass("itemName").text("Name"),
						$("<th></th>").addClass("testDate").text("Date Added"),
						$("<th></th>").addClass("keywords").text("Keywords"))),
				$tbody = $("<tbody></tbody>")));

		var root = this.root;
		$.each(root.objects, function(dbid, object)
		{
			var classes = object.classes ? object.classes : "selectable";
			var rowData = object.rowData ? object.rowData :
			{
				ttBranch: true,
				dbid: object.dbid,
				pdbid: object.pdbid,
				type: object.type,
				position: object.position,
				action: object.type + " " + object.position
			};
			var $row;
			switch (rowData.action)
			{
			case "user item":
				var user = object.db;
				$tbody.append(
					$row = $("<tr></tr>").addClass(classes).data(rowData).append(
						$("<td></td>").addClass("itemName").text("user.name").prepend(
							$("<span></span>").addClass("user"))));
				break;

			case "test before":
				rowData.ttBranch = false;
				$tbody.append(
					$row = $("<tr></tr>").addClass(classes).data(rowData).append(
						$("<td></td>").addClass("itemName").text("Create Test"),
						$("<td></td>").addClass("testDate"),
						$("<td></td>").addClass("keywords")));
				break;

			case "test item":
				var test = object.db;
				$tbody.append(
					$row = $("<tr></tr>").addClass(classes).data(rowData).append(
						$("<td></td>").addClass("itemName").text(test.name).prepend(
							$("<span></span>").addClass("test")),
						$("<td></td>").addClass("testDate").text(test.added),
						$("<td></td>").addClass("keywords").text(test.kwText)));
				break;

			case "question item":
				rowData.action = "browse";
				var question = object.db;
				$tbody.append(
					$row = $("<tr></tr>").addClass(classes).data(rowData).append(
						$("<td></td>").addClass("questionName").text("question.name").prepend(
							$("<span></span>").addClass("question"))));
				break;
			}
			// Keep the object reference against the row in a reasonably enduring way.
			root.objects[dbid].$row = $row;
		});
		var $expandeds = $tbody.children(".expanded");
		$tableTests.treetable({ expandable: true });
		$expandeds.each(function()
		{
			$tableTests.treetable("expandNode", $(this).data("dbid"));
		});
	};

	this.selectCurRow = function()
	{
		// Find out which row to select. First time through, this will be the
		// zeroth row. Otherwise it will be the row with the test (identified
		// by dbid - its database id) that was last selected. After a deletion,
		// that row will no longer exist, so the row with the same position is
		// selected instead (taking care not not go off the end of the table).
		var $rows = this.$divOuter.find(".selectable");
		var maxRow = $rows.length - 1;
		if (maxRow >= 0)
		{
			var $row = $rows.filter("#" + this.curDbid);
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
		var $oldRow = $row.siblings(".selected");
		if ($oldRow.length > 0)
		{
			$oldRow.removeClass("selected");
		}
		$row.addClass("selected");
		this.$row = $row;
		this.curIndex = $row.index();
		var dbid = this.curDbid = $row.data("dbid");
		var test;
		var create = false;
		switch ($row.data("action"))
		{
		case "test before":
			create = true;
			test = makeTestData({ });
			break;
		case "test item":
			test = this.root.objects[dbid].db;
			break;
		}

		this.menu.clearItems();
		var thisObj = this;
		var te = this.getTestEditor();
		if (test)
		{
			this.getTestEditor().select(test);
			var name = " <" + test.name + ">";
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
}
