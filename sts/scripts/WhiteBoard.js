//
// WhiteBoard.js: represents the whiteboard part of the HTML project display
// Project: STS - Specialised Test Setter
//

function WhiteBoard(root)
{
	this.root = root;

	this.root.$divBoard.empty().append(
		$("<div id='divGreeting'></div>").append(
			this.$greeting = $("<h2></h2>").append(
				this.$comment = $("<div></div>"))));

	this.setGreeting = function(greeting, comment)
	{
		this.$greeting.text(greeting);
		this.$comment.text(this.setGreeting.arguments.length > 1 ? comment : "");
	}

	this.clearGreeting = function()
	{
		this.$greeting.text("");
		this.$comment.text("");
	}
}
