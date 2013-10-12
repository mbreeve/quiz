//
// TestEditor.js: manages the HTML for the Update Test Form
// Project: STS - Specialised Test Setter
//

function TestEditor(parent)
{
	this.parent = parent;
	this.root = parent.root;
	this.launch = this.root.launch;

	this.menu = new MainMenu(this.root, "testEditor");
	this.menu.setFixed();
	this.menu.hide();

	this.root.$divForm.append(
		this.$divOuter = $("<div id='testEditor'></div>").hide().append(
			$("<table border='true' class='stand'></table>").append(
				$("<tbody></tbody>").append(
					$("<tr></tr>").append(
						$("<td class='labels'></td>").append(
							$("<label for='name'>Name</label>")),
						$("<td class='inputs'></td>").append(
							this.$name = $("<input autofocus size='80' />")
								.attr("placeholder", "A unique name"))),
					$("<tr></tr>").append(
						$("<td class='labels'></td>").append(
							$("<label for='descr'>Description</label>")),
						$("<td class='inputs'></td>").append(
							this.$descr = $("<input size='80' />")
								.attr("placeholder", "An optional description"))),
					$("<tr></tr>").append(
						$("<td class='labels'></td>").append(
							$("<label for='kwText'>Keywords</label>")),
						$("<td></td>").append(
							this.$kwText = $("<input size='80' />")
								.attr("placeholder", "Keywords separated by semi-colons")))))));

	var thisObj = this;
	this.$kwText.click(function()
	{
		thisObj.suspend().getKeywordSelector().enter(thisObj.current);
	});
	this.$divOuter.on("keyup", ".inputs", function()
	{
		thisObj.syncDisplay(true);
		thisObj.adjustMenu();
	});

	// Get the keyword selector, creating if necessary ...
	this.getKeywordSelector = function()
	{
		if (!this.keywordSelector)
		{
			this.keywordSelector = new KeywordSelector(this);
		}
		return this.keywordSelector;
	};

	// Get the question editor, creating if necessary ...
	this.getQuestionEditor = function()
	{
		if (!this.questionEditor)
		{
			this.questionEditor = new QuestionEditor(this);
		}
		return this.questionEditor;
	};

	// Get the questions displayer, creating if necessary ...
	this.getQuestionsLister = function()
	{
		if (!this.questionsLister)
		{
			this.questionsLister = new QuestionsLister(this);
		}
		return this.questionsLister;
	};

	// This is the response to ajax calls, i.e. asynchronously to everything else.
	this.dispatch = function(data)
	{
		switch (data.request)
		{
		case "createTest":
		case "updateTest":
		case "deleteTest":
			return true;
		default:
			return this.getQuestionsLister().dispatch(data);
		}
	}

	this.select = function(test)
	{
		this.test = test;
		this.original = makeTestData({ source: test });
		this.current = makeTestData({ source: test });
		this.creating = (test.idTest == 0);
		if (!this.creating)
		{
			this.readQuestions(test);
		}
		this.syncDisplay();
		return this;
	};

	this.syncDisplay = function(toNotFromData)
	{
		if (toNotFromData)
		{
			this.current.name = this.$name.val();
			this.current.descr = this.$descr.val();
		}
		else
		{
			this.$name.val(this.current.name);
			this.$descr.val(this.current.descr);
			this.$kwText.val(this.current.kwText);
		}
		return this;
	};

	// Enter this from the calling (parent) browser/editor
	this.enter = function()
	{
		this.root.whiteBoard.setGreeting("View/Edit Test");
		this.$divOuter.show();
		this.showOptions();
		return this;
	};

	// Finish this and return to the calling (parent) browser/editor
	this.finish = function()
	{
		this.menu.hide();
		this.$divOuter.hide();
		this.root.whiteBoard.clearGreeting();
		return this;
	};

	// Save state on this browser/editor before calling enter() on a child
	this.suspend = function()
	{
		this.$divOuter.hide();
		this.root.whiteBoard.clearGreeting();
		this.menu.hide();
		return this;
	};

	// Resume in this browser/editor after returning from finish() on a child
	this.resume = function(test)
	{
		this.menu.show();
		this.root.whiteBoard.setGreeting("View/Edit Test");
		this.current = makeTestData({ source: test });
		this.syncDisplay();
		this.$divOuter.show();
		this.showOptions();
		this.adjustMenu();
		return this;
	};

	// Show menu options, and set callbacks, appropriate to the context
	this.showOptions = function()
	{
		var thisObj = this;
		this.menu.clearItems();

		var name = this.creating ? " New Test" : " <" + this.original.name + ">";

		this.idSave = this.menu.addItem("Save" + name, false, function()
		{
			thisObj.save();
		});
		this.idAbandon = this.menu.addItem("Abandon" + name, false, function()
		{
			thisObj.abandon();
		});
		this.idDelete = this.creating ? 0 : this.menu.addItem("Delete" + name, true, function()
		{
			thisObj.delete();
		});
		this.idAddQuestions = this.menu.addItem("Add Questions", true, function()
		{
			thisObj.addQuestions();
		});
		this.idListTests = this.menu.addItem("Back to <List Tests>", true, function()
		{
			thisObj.finish().parent.resume();
		});

		this.menu.show();
		return this;
	};

	this.addQuestions = function()
	{
		// Set the data for the tests, go to the next level, and display thetests ...
		var qe = this.getQuestionEditor();
		qe.select(makeQuestionData({ idTest: this.current.idTest }));
		this.suspend();
		qe.enter();
		return this;
	};

	this.abandon = function()
	{
		this.current = makeTestData({ source: this.original });
		this.syncDisplay();
		this.adjustMenu();
		return this;
	};

	this.save = function()
	{
		this.launch.postAjax(
		{
			class: "TestsManager",
			method: this.creating ? "createTest" : "updateTest",
			args:
			{
				idSetter: this.root.idSetter,
				idTest: this.current.idTest,
				name: this.current.name,
				descr: this.current.descr,
				keywords: this.current.keywords
			}
		});

		this.original = makeTestData({ source: this.current });
		this.creating = false;
		this.adjustMenu();
		return this;
	};

	this.delete = function()
	{
		if (confirm("Do you want to delete " + this.current.name + "?"))
		{
			this.launch.postAjax(
			{
				class: "TestsManager",
				method: "deleteTest",
				args:
				{
					idSetter: this.root.idSetter,
					idTest: this.current.idTest
				}
			});
		}
		this.finish().parent.resume();
		return this;
	};

	this.readQuestions = function()
	{
		this.launch.postAjax(
		{
			class: "QuestionsManager",
			method: "readQuestions",
			args:
			{
				idTest: this.current.idTest
			}
		});
		return this;
	};

	this.adjustMenu = function()
	{
		var enable = this.current.name.length > 0 &&
			(this.creating ||
			this.original.name != this.current.name ||
			this.original.descr != this.current.descr ||
			this.original.kwText != this.current.kwText);

		this.menu.enableItem(this.idSave, enable);
		this.menu.enableItem(this.idAbandon, enable);
		if (this.idDelete > 0)
		{
			this.menu.enableItem(this.idDelete, !enable);
		}
		this.menu.enableItem(this.idListTests, !enable);
		return this;
	};
}
