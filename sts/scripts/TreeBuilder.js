//
// TreeBuilder.js: builds and maintains a javascript tree used for
// viewing tests and questions
// Project: STS - Specialised Test Setter
//

function makeTestData(from)
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
		if (response.database)
		{
			this.copyKeywords(response.database.keywords);		// copy across the keywords
			this.copyTests(response.database.tests);          // copy across the tests
			this.getTreeViewer().updateTests();
		}
		switch (response.request)
		{
		case "setSetter":
			return true;
		default:
			return this.getTreeViewer().dispatch(response);
		}
	}

	// Copy across the keywords
	this.copyKeywords = function(keywords)
	{
		if (keywords)
		{
			var root = this.root;
			root.allKeywords = { };
			$.each(keywords, function(lc, keyword)
			{
				root.allKeywords[lc] = keyword;
			});
		}
	}

	// Copy across the tests
	this.copyTests = function(tests)
	{
		if (tests)
		{
			var oldObjects = this.root.objects;
			var newObjects = { };
	
			var dbid = "T-Before";
			newObjects[dbid] =
			{
				type: "test",
				position: "before",
				dbid: dbid
			};
	
			$.each(tests, function()
			{
				var dbid = "T-" + this.idTest;
				var object;
				if (oldObjects && oldObjects[dbid])
				{
					object = oldObjects[dbid];
					object.rowData = object.$row.data();
					object.classes = object.$row.attr("class");
				}
				else
				{
					object =
					{
						type: "test",
						position: "item",
						dbid: dbid
					};
				}
				object.db = makeTestData({ source: this });
				newObjects[dbid] = object;
			});
	
			var dbid = "T-After";
			newObjects[dbid] =
			{
				type: "test",
				position: "after",
				dbid: dbid
			};
	
			this.root.objects = newObjects;
		}
	}
}
