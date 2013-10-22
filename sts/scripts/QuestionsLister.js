//
// QuestionsLister.js: manages the HTML for the displaying the list of questions
// Project: STS - Specialised Test Setter
//

function makeQuestionData(from)
{
	// The default object, used when there is no source object ...
	var obj =
	{
		idTest: from.idTest ? from.idTest : 0,
		idQuestion: 0,
		query: "",
		sequence: 0,
		idAnswer: 0,
		reply: ""
	};
	if (from.source)
	{
		// Essentially, a deep clone into obj ...
		$.extend(true, obj, from.source);
	}
	return obj;
}

function QuestionsLister(parent)
{
	this.parent = parent;
	this.root = parent.root;
	this.launch = this.root.launch;

	this.curIndex = 0;
	this.ttid = 0;
	this.menu = new MainMenu(this.root, "questionsLister");
	this.menu.setFixed();
	this.menu.hide();

	this.root.$divForm.append(
		this.$divOuter = $("<div id='questionsLister'></div>").hide().append(
			$("<table class='stand'></table>").append(
				this.$tbody = $("<tbody></tbody>"))));

	var thisObj = this;
	this.$divOuter.on("click", ".selectable", function()
	{
		thisObj.selectRow(this).showOptions();
	});

	// Get the question editor, creating if necessary
	this.getQuestionEditor = function()
	{
		if (!this.questionEditor)
		{
			this.questionEditor = new QuestionEditor(this);
		}
		return this.questionEditor;
	};

	// This is the response to ajax calls, i.e. asynchronously to everything else.
	this.dispatch = function(data)
	{
		var root = this.root;
		//switch (data.request)
		switch (data.method)
		{
		case "readQuestions":
			// Copy across the questions of the given test ...
			var test = root.allTests["Q-" + data.idTest];
			test.questions = { };
			$.each(data.questions, function()
			{
				var question = makeQuestionData({ source: this });
				test.questions[this.idQuestion] = question;
			});
			return true;
		default:
			return this.getQuestionEditor().dispatch(data);
		}
	}

	// Finish this and return to the calling (parent) browser/editor
	this.finish = function()
	{
		this.menu.hide();
		this.$divOuter.hide();
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

	// Enter/resume this from a calling parent, or a returning child, browser/editor.
	// New data has been passed in.
	this.enter = function(test)
	{
		if (test !== undefined)
		{
			this.test = test;
		}
		this.displayAll();
		this.menu.show();
		this.$divOuter.show();
		this.root.whiteBoard.setGreeting("Browse test <" + this.test.name + ">");
		return this;
	};

	this.displayAll = function()
	{
		var $tbody = this.$tbody;
		$tbody.empty();

		var test = this.root.allTests["T-" + this.test.idTest];
		$.each(test.questions, function()
		{
			$tbody.append(
				$("<tr></tr>").addClass("selectable").attr("id", this.idQuestion).append(
					$("<td></td>").addClass("question").text(this.query),
					$("<td></td>").addClass("answer").text(this.reply)));
		});
		$tbody.append(
			$("<tr></tr>").addClass("selectable").attr("id", 0).append(
				$("<td></td>").addClass("question").text("Add Question"),
				$("<td></td>").addClass("answer")));

		// Find out which row to select. First time through, this will be the
		// zeroth row. Otherwise it will be the row with the question (identified
		// by ttid - its database id) that was last selected. After a deletion,
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
			$row.click();
		}
		return this;
	};

	this.selectRow = function(row)
	{
		var $row = $(row);
		$row.addClass("selected").siblings().removeClass("selected");
		this.curIndex = $row.index();
		var ttid = this.ttid = parseInt($row.attr("id"));
		this.question = ttid == 0 ? makeQuestionData({ }) : this.root.allQuestions[ttid];
		this.getQuestionEditor().select(this.question);
		return this;
	};

	// Show menu options, and set callbacks, appropriate to the context
	this.showOptions = function()
	{
		var thisObj = this;
		this.menu.clearItems();
/*
		var name = " <" + this.question.name + ">";
		if (this.question.idQuestion == 0)
		{
			this.menu.addItem("Create New Question", true, function()
			{
				thisObj.suspend().getQuestionEditor().enter();
			});
		}
		else
		{
			this.menu.addItem("View" + name, true, function()
			{
				thisObj.suspend().getQuestionEditor().enter();
			});
			this.menu.addItem("Delete" + name, true, function()
			{
				thisObj.suspend().getQuestionEditor().delete();
			});
		}
*/
		this.idTestEditor = this.menu.addItem("Back to Test Editor", true, function()
		{
			thisObj.finish().parent.resume();
		});

		this.menu.show();
		return this;
	};
}
