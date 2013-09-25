//
// QuestionEditor.js: creates and edits questions within a test
// Project: STS - Specialised Test Setter
//

function QuestionEditor(parent)
{
	this.parent = parent;
	this.root = parent.root;
	this.launch = this.root.launch;

	this.menu = new MainMenu(this.root, "questionEditor");
	this.menu.setFixed();
	this.menu.hide();

	this.root.$divForm.prepend(
		this.$divOuter = $("<div id='questionEditor'></div>").hide().append(
			$("<table border='true' class='stand'></table>").append(
				$("<tbody></tbody>").append(
					$("<tr></tr>").append(
						$("<td class='labels'></td>").append(
							$("<label for='name'>Question</label>")),
						$("<td></td>").append(
							this.$query = $("<input class='inputs' size='80' />"))),
					$("<tr></tr>").append(
						$("<td class='labels'></td>").append(
							$("<label for='descr'>Answer</label></td>")),
						$("<td></td>").append(
							this.$reply = $("<input class='inputs' size='80' />")))))));

	var thisObj = this;
	this.$divOuter.on("keyup", ".inputs", function()
	{
		thisObj.syncDisplay(true);
		thisObj.lookForChanges();
	});

	// This is the response to ajax calls, i.e. asynchronously to everything else.
	this.dispatch = function(data)
	{
		switch (data.request)
		{
		case "createQuestion":
		case "updateQuestion":
			return true;
		default:
			return false;
		}
	}

	this.select = function(question)
	{
		this.original = makeQuestionData({ source: question });
		this.current = makeQuestionData({ source: question });
		this.creating = (question.idTest == 0);
		this.syncDisplay();
		return this;
	}

	this.syncDisplay = function(toNotFromData)
	{
		if (toNotFromData)
		{
			this.current.query = this.$query.val();
			this.current.reply = this.$reply.val();
		}
		else
		{
			this.$query.val(this.current.query);
			this.$reply.val(this.current.reply);
		}
		return this;
	};

	// Enter this from the calling (parent) browser/editor
	this.enter = function()
	{
		this.root.whiteBoard.setGreeting("View/Edit Question");
		this.$divOuter.show();
		this.showOptions();
		return this;
	}

	// Finish this and return to the calling (parent) browser/editor
	this.finish = function()
	{
		this.menu.hide();
		this.$divOuter.hide();
		this.root.whiteBoard.clearGreeting();
		return this;
	};

	// Show menu options, and set callbacks, appropriate to the context
	this.showOptions = function()
	{
		var thisObj = this;
		this.menu.clearItems();

		this.idSave = this.menu.addItem("Save", false, function()
		{
			thisObj.save();
		});
		this.idAbandon = this.menu.addItem("Abandon", false, function()
		{
			thisObj.abandon();
		});
		this.idDelete = this.creating ? 0 : this.menu.addItem("Delete", true, function()
		{
			thisObj.delete();
		});
		this.idEndQuestions = this.menu.addItem("Finish Questions", true, function()
		{
			thisObj.finish().parent.resume();
		});

		this.menu.show();
		return this;
	};

	this.lookForChanges = function()
	{
		var enable =
			this.current.query.length > 0 &&
			this.current.reply.length > 0 &&
			(this.original.query != this.current.query ||
			this.original.reply != this.current.reply);

		this.menu.enableItem(this.idSave, enable);
		this.menu.enableItem(this.idAbandon, enable);
		if (this.idDelete > 0)
		{
			this.menu.enableItem(this.idDelete, !enable);
		}
		this.menu.enableItem(this.idEndQuestions, !enable);
		return this;
	};

	this.abandon = function()
	{
		this.current = makeQuestionData({ source: this.original });
		this.syncDisplay();
		this.lookForChanges();
		return this;
	};

	this.save = function()
	{
		this.launch.postAjax(
		{
			class: "QuestionsManager",
			method: thisObj.creating ? "createQuestion" : "updateQuestion",
			args:
			{
				idTest: this.current.idTest,
				query: this.current.query,
				reply: this.current.reply
			}
		});

		this.original = makeQuestionData({ source: this.current });
		this.lookForChanges();
		return this;
	};
}
