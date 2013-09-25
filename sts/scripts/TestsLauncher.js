//
// TestsLauncher.js: start off js test handling, containg "onDocumentLoad".
// Project: STS - Specialised Test Setter
//

// The TestsLauncher object, providing functionality to communicate via Ajax, and
// to include other js modules.
function TestsLauncher()
{
	this.postAjax = function()
	{
		this.tasks = [];
		var tasks = this.postAjax.arguments;
		var thisObj = this;
		$.each(tasks, function()
		{
			if (this)
			{
				thisObj.tasks.push(
				{
					class: this.class,
					method: this.method,
					args: this.args,
				});
			}
		});
		var args =
		[
			{
				name: "page",
				value: "QueryTests"
			},
			{
				name: "json",
				value: true
			},
			{
				name: "tasks",
				value: JSON.stringify(this.tasks)
			}
		];
		//alert("requested: " + args[2].value);
		$.post("index.php", args, function(json)
		{
			//alert("walk-through: " + json.walk);
			$.each(json.tasks, function()
			{
				thisObj.dispatch(this);
			});
		}, "json");
	};

	// This is the response to ajax calls, i.e. asynchronously to everything else.
	this.dispatch = function(response)
	{
		if (response.success)
		{
			switch (response.data.request)
			{
			case "getFixedData":
				if (!this.testsRoot)
				{
					this.testsRoot = new TestsRoot(this, response.data);
					this.testsRoot.enter();
				}
				return true;
			default:
				return this.testsRoot ? this.testsRoot.dispatch(response.data) : false;
			}
		}
		return false;
	}

	// Exit from this JS session, and return to PHP home page
	this.exitTests = function()
	{
		window.location.href="index.php?page=HomePage";
	};
}

$(function()
{
	"use strict";

	var launch = new TestsLauncher();    // Ajax comms & include other js modules

	// Just get the fixed data from the server ...
	launch.postAjax(
	{
		class: "FixedData",
		method: "getFixedData",
		args: [ ]
	});
});
