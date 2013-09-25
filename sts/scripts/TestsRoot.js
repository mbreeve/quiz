//
// TestsRoot.js: accept data from ajax, and pass it on
// Project: STS - Specialised Test Setter
//

function TestsRoot(parent, data)
{
	this.launch = parent;

	this.$divBoard = $("#" + data.html.divBoard);
	this.whiteBoard = new WhiteBoard(this);
	this.$divMenu = $("#" + data.html.divMenu);
	this.$divMenu.empty();
	this.$divForm = $("#" + data.html.divForm);
	this.$divForm.empty();
	this.idOwner = data.owner.idOwner;

	this.enter = function()
	{
		this.launch.postAjax(
		{
			class: "TestsManager",
			method: "readTests",
			args:
			{
				idOwner: this.idOwner
			}
		});
	}

	// Get the tree builder object, creating if necessary
	this.getTreeBuilder = function()
	{
		if (!this.treeBuilder)
		{
			this.treeBuilder = new TreeBuilder(this);
		}
		return this.treeBuilder;
	};

	// This is the response to ajax calls, i.e. asynchronously to everything else.
	this.dispatch = function(data)
	{
		return this.getTreeBuilder().dispatch(data);
	};

	this.resume = function()
	{
		this.launch.exitTests();
	}
}
