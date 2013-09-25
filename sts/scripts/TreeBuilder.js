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
	this.dispatch = function(data)
	{
		switch (data.request)
		{
		case "readTests":
			this.copyKeywords(data);				// copy across the keywords
			this.copyTests(data);						// copy across the tests
			this.getTreeViewer().enter();		// update our view
			return true;

		default:
			return this.getTreeViewer().dispatch(data);
		}
	}

	// Copy across the keywords
	this.copyKeywords = function(data)
	{
		var root = this.root;
		root.allKeywords = { };
		$.each(data.keywords, function(lc, keyword)
		{
			root.allKeywords[lc] = keyword;
		});
	}

	// Copy across the tests
	this.copyTests = function(data)
	{
		var oldItems = this.root.items;
		var newItems = { };

		var dbid = "T-Before";
		newItems[dbid] =
		{
			type: "test",
			position: "before",
			dbid: dbid
		};

		$.each(data.tests, function()
		{
			var dbid = "T-" + this.idTest;
			if (oldItems && oldItems[dbid])
			{
				newItems[dbid] = oldItems[dbid];
			}
			else
			{
				newItems[dbid] =
				{
					type: "test",
					position: "list",
					data: makeTestData({ source: this }),
					dbid: dbid
				};
			}
		});

		var dbid = "T-After";
		newItems[dbid] =
		{
			type: "test",
			position: "after",
			dbid: dbid
		};
		
		this.root.items = newItems;
	}
}
