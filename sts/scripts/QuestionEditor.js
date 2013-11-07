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
							$("<label for='ident'>Identification</label>")),
						$("<td class='inputs'></td>").append(
							this.$ident = $("<input autofocus size='80' />")
								.attr("placeholder", "A unique identifier"))),
					$("<tr></tr>").append(
						$("<td class='labels'></td>").append(
							$("<label for='ident'>Question</label>")),
						$("<td></td>").append(
							this.$query = $("<input class='inputs' size='80' />")
								.attr("placeholder", "The question"))),
					$("<tr></tr>").append(
						$("<td class='labels'></td>").append(
							$("<label for='descr'>Answer</label></td>")),
						$("<td></td>").append(
							this.$reply = $("<input class='inputs' size='80' />")
								.attr("placeholder", "The answer")))))));

	var thisObj = this;
	this.$divOuter.on("keyup", ".inputs", function()
	{
		thisObj.syncDisplay(true);
		thisObj.lookForChanges();
	});

	this.syncDisplay = function(fromScreen)
	{
		if (fromScreen)		// syncDisplay(true)
		{
			this.question.fields.ident = this.$ident.val();
			this.question.fields.query = this.$query.val();
			this.question.fields.reply = this.$reply.val();
		}
		else							// syncDisplay(false)
		{
			this.$ident.val(this.question.fields.ident);
			this.$query.val(this.question.fields.query);
			this.$reply.val(this.question.fields.reply);
		}
		return this;
	};

	// Create a new question within the given test.
	this.create = function(question, idTest)
	{
		this.creating = true;
		this.idTest = idTest;
		this.question = question;
		var greeting = "Create Question";
		this.edit();
	};

	// Update a question.
	this.update = function(question, idTest)
	{
		this.creating = false;
		this.idTest = idTest;
		this.question = question;
		this.greeting = "Update Question";
		this.edit();
	};

	// Common part of update() & create().
	this.edit = function()
	{
		this.question.original = $.extend(true, { }, this.question.fields);
		this.idQuestion = this.question.dbid;
		this.syncDisplay(false);
		this.root.whiteBoard.setGreeting(this.greeting);
		this.$divOuter.show();
		this.showOptions();
		return this;
	};

	this.delete = function(question)
	{
		if (confirm("Do you want to delete " + question.fields.ident + "?"))
		{
			this.launch.postAjax(
			{
				class: "TestsManager",
				method: "deleteQuestion",
				args:
				{
					idQuestion: question.dbid
				}
			});
		}
		this.finish().parent.resume();
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

	// Show menu options, and set callbacks, appropriate to the context
	this.showOptions = function()
	{
		var thisObj = this;
		this.menu.clearItems();
		var ident = this.creating ? " New Question" : " <" + this.question.fields.ident + ">";
		this.idSave = this.menu.addItem("Save" + ident, false, function()
		{
			thisObj.save().finish().parent.resume();
		});
		this.idAbandon = this.menu.addItem("Abandon", false, function()
		{
			thisObj.abandon().finish().parent.resume();
		});
		this.idList = this.menu.addItem("Back to <List Questions>", true, function()
		{
			thisObj.finish().parent.resume();
		});
		this.menu.show();
		return this;
	};

	this.lookForChanges = function()
	{
		var enable = this.question.fields.ident.length > 0 &&
			(this.creating ||
			this.question.original.ident != this.question.fields.ident ||
			this.question.original.query != this.question.fields.query ||
			this.question.original.reply != this.question.fields.reply);

		this.menu.enableItem(this.idSave, enable);
		this.menu.enableItem(this.idAbandon, enable);
		this.menu.enableItem(this.idList, !enable);
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
			class: "TestsManager",
			method: this.creating ? "createQuestion" : "updateQuestion",
			args:
			{
				idSetter: this.question.path.setter,
				idTest: this.idTest,
				idQuestion: this.question.dbid,
				ident: this.question.fields.ident,
				query: this.question.fields.query,
				reply: this.question.fields.reply
			}
		});
		if (this.creating)
		{
			$.extend(true, this.question.fields, this.question.original);
			this.creating = false;
		}
		else
		{
			this.question.original = { };
			$.extend(true, this.question.original, this.question.fields);
			this.lookForChanges();
		}
		return this;
	};
}
