//
// TreeBuilder.js: builds and maintains a javascript tree used for
// viewing tests and questions
// Project: STS - Specialised Test Setter
//

function TreeBuilder(parent)
{
	this.root = this.parent = parent;
	this.launch = this.root.launch;

	// Get the tree viewer object, creating if necessary
	this.getTreeViewer = function()
	{
		if (!this.treeViewer)
		{
			this.treeViewer = new TreeViewer(this);
		}
		return this.treeViewer;
	};

	// This is the response to ajax calls, i.e. asynchronously to everything else.
	this.dispatch = function(response)
	{
		var obj = this;
		try
		{
			$.each(response.database, function()
			{
				switch (this.action)
				{
				case "list":
					switch (this.table)
					{
					case "logged":
						obj.copyLogged(this);
						break;
					case "setter":
						obj.copySetters(this);
						break;
					case "keyword":
						obj.copyKeywords(this);
						break;
					case "test":
						obj.copyTests(this);
						break;
					case "question":
						obj.copyQuestions(this);
						break;
					}
					break;
				case "delete":
					switch (this.table)
					{
					case "logged":
						break;
					case "setter":
						break;
					case "keyword":
						break;
					case "test":
						break;
					case "question":
						obj.deletedQuestion(this);
						break;
					}
					break;
				}
			});
			if (response.database.length > 0)
			{
				this.getTreeViewer().updateTree();
			}
		}
		catch (msg)
		{
			alert("Fatal error in database transfer: " + msg);
		}
		return this.getTreeViewer().dispatch(response);
	};

	// Copy across the logged on user: definitely expect one with id = 0. This must
	// happen before any of the other copy...()
	this.copyLogged = function(data)
	{
		var root = this.root;
		root.logged = { idSetters: { } };
		root.setters = { };
		root.tests = { };
		root.questions = { };
		var logged = root.logged;
		$.each(data.records, function()
		{
			logged.fields = this;
			return false;		// exit after just one
		});
	};

	// Copy across the setter(s): normally expect one!
	this.copySetters = function(data)
	{
		var root = this.root;
		var logged = root.logged;
		// Clear out the setter ids, and start again ...
		logged.idSetters = { };
		// Isolate the original setters, and start a new set ...
		var setters = root.setters;
		root.setters = { };
		$.each(data.records, function()
		{
			var idSetter = this.idSetter;
			var setter = setters[idSetter];
			if (!setter)
			{
				setter =
				{
					dbid: idSetter,
					path:
					{
						setter: idSetter
					},
					fields: { },
					keywords: { },
					tests: { }
				};
			}
			$.extend(true, setter.fields, this);
			root.setters[idSetter] = setter;
			logged.idSetters[idSetter] = idSetter;
		});
	};

	// Copy across the keywords
	this.copyKeywords = function(data)
	{
		var root = this.root;
		var idSetter = data.idSetter;
		var setter = root.setters[idSetter];
		if (!setter)
		{
			throw "setter <" + idSetter + "> does not exist";
		}
		setter.keywords = { };
		$.each(data.records, function(lc, keyword)
		{
			setter.keywords[lc] = keyword;
		});
	};

	// Copy across the tests
	this.copyTests = function(data)
	{
		var root = this.root;
		var idSetter = data.idSetter;
		var setter = root.setters[idSetter];
		if (!setter)
		{
			throw "setter <" + idSetter + "> does not exist";
		}
		// Isolate the original tests and start a new set ...
		var oldTests = { };
		if (setter.idTests)
		{
			$.each(setter.idTests, function()
			{
				var idTest = this;
				oldTests[idTest] = root.tests[idTest];
				root.tests[idTest] = null;
			});
		}
		setter.idTests = { };
	
		// Add a dummy test record to cater for question creation, then go through
		// all the question records, including the dummy ...
		var records = { new: { idTest: "0" } };
		$.each($.extend(records, data.records), function()
		{
			var idTest = this.idTest;
			var test = oldTests[idTest];
			if (!test)
			{
				test =
				{
					dbid: idTest,
					path:
					{
						setter: idSetter,
						test: idTest
					},
					fields:
					{
						descr: "",
						ident: "",
						keywords: { },
						get kwText()
						{
							var list = [];
							$.each(this.keywords, function(key, value)
							{
								if (value)
								{
									list.push(value);
								}
							});
							return list.length > 0 ? list.join("; ") : "";
						}
					},
					questions: { }
				};
			}
			$.extend(true, test.fields, this);
			root.tests[idTest] = test;
			setter.idTests[idTest] = idTest;
		});
	};

	// Copy across the questions
	this.copyQuestions = function(data)
	{
		var root = this.root;
		var idSetter = data.idSetter;
		var idTest = data.idTest;
		var test = root.tests[idTest];
		if (!test)
		{
			throw "test <" + idTest + "> does not exist";
		}		
		// Isolate the original questions and start a new set ...
		var oldQuestions = { };
		if (test.idQuestions)
		{
			$.each(test.idQuestions, function()
			{
				var idQuestion = this;
				oldQuestions[idQuestion] = root.questions[idQuestion];
				root.questions[idQuestion] = null;
			});
		}
		test.idQuestions = { };

		// Add a dummy question record to cater for question creation, then go through
		// all the question records, including the dummy ...
		var records = { new: { idQuestion: "0" } };
		$.each($.extend(records, data.records), function()
		{
			var idQuestion = this.idQuestion;
			var question = oldQuestions[idQuestion] ||
			{
				dbid: idQuestion,
				path:
				{
					setter: idSetter,
					test: idTest,
					question: idQuestion
				},
				fields:
				{
					ident: "",
					query: "",
					reply: ""
				},
				parent: test
			};
			$.extend(true, question.fields, this);
			root.questions[idQuestion] = question;
			test.idQuestions[idQuestion] = idQuestion;
		});
	};

	// Notification that a question has been deleted
	this.deletedQuestion = function(data)
	{
		var idQuestion = data.dbid;
		var questions = this.root.questions;
		var test = questions[idQuestion].parent;
		delete questions[idQuestion];
		delete test.idQuestions[idQuestion];
	};
}
