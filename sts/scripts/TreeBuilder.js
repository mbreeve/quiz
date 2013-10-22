//
// TreeBuilder.js: builds and maintains a javascript tree used for
// viewing tests and questions
// Project: STS - Specialised Test Setter
//

function makeTestFields(from)
{
	// The default object, used when there is no source object ...
	var obj =
	{
		idTest: 0,
		added: "",
		name: "",
		descr: "",
		keywords: { },
		questions: { },
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
	};
	if (from.source)
	{
		// Essentially, a deep clone into obj ...
		$.extend(true, obj, from.source);
	}
	return obj;
}

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
		$.each(response.database, function()
		{
			switch (this.table)
			{
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
		});
		this.getTreeViewer().updateTests();
		return this.getTreeViewer().dispatch(response);
	}

	// Copy across the setter(s): normally expect one!
	this.copySetters = function(setters)
	{
		var root = this.root;
		var oldSetters = root.setters;
		oldSetters || (oldSetters = { });
		root.setters = { };
		$.each(setters.records, function()
		{
			var setter;
			var idSetter = "S-" + this.idSetter;
			(setter = oldSetters[idSetter]) || (setter =
			{
				fields: { },
				keywords: { },
				tests: { }
			});
			setter.fields = { name: this.setterName };
			root.setters[idSetter] = setter;
		});
	}

	// Copy across the keywords
	this.copyKeywords = function(keywords)
	{
		var idSetter = "S-" + keywords.pid;
		var setter = this.root.setters[idSetter];
		setter.keywords = { };
		$.each(keywords.records, function(lc, keyword)
		{
			setter.keywords[lc] = keyword;
		});
	}

	// Copy across the tests
	this.copyTests = function(tests)
	{
		var root = this.root;
		var idSetter = "S-" + tests.pid;

		root.setters || (root.setters = { });
		root.setters[idSetter] || (root.setters[idSetter] = { });
		var setters = root.setters;
		var oldTests = setters[idSetter].tests;
		oldTests || (oldTests = { });
		setters[idSetter].tests = { };
		$.each(tests.records, function()
		{
			var test;
			var idTest = "T-" + this.idTest;
			(test = oldTests[idTest]) || (test =
			{
				fields: { },
				questions: { }
			});
			test.fields = makeTestFields({ source: this });
			setters[idSetter].tests[idTest] = test;
		});
	}
}
