//
// MainMenu.js: define the menu for each browser/editor
// Project: STS - Specialised Test Setter
//

function MainMenu(root, marker)
{
	this.root = root;

	this.root.$divMenu.append(
		this.$divOuter = $("<div id='" + marker + "'></div>"));
	var thisObj = this;
	this.$divOuter.on("click", ".menuselectable", function()
	{
		thisObj.callbacks[$(this).attr("id")]();
	});

	this.clearItems = function()
	{
		this.$divOptions.empty();
		this.callbacks = [ ];
	};

	this.setFixed = function()
	{
		this.$divOuter.empty().append(
			this.$divStyle = $("<div></div>").append(
				$("<h2 class='big'>Options</h2>"),
				this.$divOptions = $("<div></div>")));
		this.clearItems();
	};

	this.setPopup = function()
	{
		this.$divOuter.empty().append(
			this.$divStyle = $("<div class='popup'></div>").append(
				$("<h2 class='big'>Options</h2>"),
				this.$divOptions = $("<div></div>")));
		this.$divStyle.css(
		{
			left: ($(window).width() - 50) / 2,
			top: ($(window).height() - 100) / 4
		});
		this.clearItems();
	};

	this.close = function()
	{
		this.$divOuter.remove();
	};

	this.show = function()
	{
		this.$divOuter.show();
	};

	this.hide = function()
	{
		this.$divOuter.hide();
	};

	this.empty = function()
	{
		this.$divOuter.empty();
	};

	this.addItem = function(text, enabled, action)
	{
		var id = this.callbacks.push(action) - 1;
		this.$divOptions.append(
			$anchor = $("<a href='#'></a>")
				.text(text)
				.addClass(enabled ? "menuselectable" : "menudisabled")
				.attr("id", id));
		return id;
	};

	this.enableItem = function(id, enabled)
	{
		var $anchor = this.$divOptions.find("#" + id);
		if (enabled)
		{
			$anchor.removeClass("menudisabled").addClass("menuselectable");
		}
		else
		{
			$anchor.removeClass("menuselectable").addClass("menudisabled");
		}
	};

	this.select = function(id)
	{
		this.$divOptions.find(id ? "#" + id : "tr:first").click();
	};
}
