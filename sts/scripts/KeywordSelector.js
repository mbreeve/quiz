//
// KeywordSelector.js: adds/removes keywords for a test.
// Project: STS - Specialised Test Setter
//

function KeywordSelector(parent)
{
	this.parent = parent;
	this.root = parent.root;
	this.launch = this.root.launch;

	this.menu = new MainMenu(this.root, "keywordSelector");
	this.menu.setFixed();
	this.menu.hide();

	this.root.$divForm.append(
		this.$divOuter = $("<div id='keywordSelector'></div>").hide().append(
			$("<table border='true' class='stand'></table>").append(
				$("<tbody></tbody>").append(
					$("<tr></tr>").append(
						$("<td class='labels'></td>").append(
							$("<label for='kwText'>Text</label>")),
						$("<td></td>").append(
							this.$kwText = $("<input size='80' />"))),
					$("<tr></tr>").append(
						$("<td class='labels'></td>").append(
							$("<label for='cboxes'>Check Boxes</label>")),
						$("<td></td>").append(
							this.$cboxes = $("<div class='kwlist' id='cboxes'></div>")))))));

	var thisObj = this;
	this.$divOuter.on("change", ".cbox", function()
	{
		thisObj.deriveKeywords();
		thisObj.showCboxes();
		thisObj.syncDisplay();
		thisObj.lookForChanges();
	});

	this.syncDisplay = function()
	{
		this.$kwText.val(this.current.kwText);
		return this;
	};

	// Enter this from the calling (parent) browser/editor
	this.enter = function(data)
	{
		this.root.whiteBoard.setGreeting("Select Keywords");
		this.original = makeTestData({ source: data });
		this.current = makeTestData({ source: data });
		this.showCboxes();
		this.$divOuter.show();
		this.showOptions();
		this.syncDisplay();
		this.lookForChanges();
		return this;
	}

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

		this.idUse = this.menu.addItem("Use changes", true, function()
		{
			thisObj.finish().parent.resume(thisObj.current);
		});
		this.idDiscard = this.menu.addItem("Discard changes", true, function()
		{
			thisObj.finish().parent.resume(thisObj.original);
		});
		this.menu.show();
		return this;
	};

	// Take the keywords data for this test, i.e. (only) those keywords which are required
	// (have been previously checked), and merge with the set of all keywords, i.e.
	// including all those that aren't required for this particular test. From the result,
	// produce a new set of checkboxes.
	this.showCboxes = function()
	{
		var keywords = this.current.keywords;
		var allKeywords = this.root.allKeywords;

		// The $.extend() merges together all checked keywords, including new ones,
		// to the names in allKeywords (the original set).
		var newKeywords = { };
		$.extend(newKeywords, allKeywords, keywords);

		var $curRow = $("<ul></ul>");
		this.$cboxes.empty().append($curRow);
		var thisObj = this;
		var count = 0;
		$.each(newKeywords, function(key, name)
		{
			$curRow.append(
				$("<li class='keyword'></li>").append(
					$("<input type='checkbox' class='cbox' />")
						.prop("checked", keywords[key]),
					$("<input type='text' class='kwinput' />")
						.val(name)
						.prop("disabled", true)));
			if (++count % 4 == 0)
			{
				$curRow = $("<ul></ul>");
				thisObj.$cboxes.append($curRow);
			}
		});

		var $xtraCbox = $("<input type='checkbox' class='cbox' />");
		var $xtraKeyword = $("<input type='text' class='kwinput' />");
		$curRow.append(
			$("<li class='keyword'></li>").append(
				$xtraCbox.prop("checked", false).prop("disabled", true),
				$xtraKeyword.attr("placeholder", "New keyword")));

		$xtraKeyword.keyup(function()
		{
			$xtraCbox.prop("disabled", $xtraKeyword.val().length == 0);
		});
		return this;
	};

	// Go through the checkboxes finding which keywords are required for this test, and
	// produce an associative array representing the set of required keywords for this test.
	// The set of available, but unwanted, keywords is ignored as far as this function is
	// concerned.
	this.deriveKeywords = function()
	{
		var keywords = { };
		var allKeywords = this.root.allKeywords;

		this.$cboxes.find("li").each(function()
		{
			var checked = false;
			var keyword = "";

			$(this).children().each(function()
			{
				var type = $(this).attr("type");
				switch (type)
				{
				case "checkbox":
					checked = $(this).prop("checked");
					break;
				case "text":
					keyword = $(this).val();
					break;
				}
			});
			if (checked && keyword.length > 0)
			{
				var lc = keyword.toLowerCase();
				if (!keywords[lc])
				{
					var known = allKeywords[lc];
					keywords[lc] = known ? known : keyword;
				}
			}
		});
		this.current.keywords = keywords;
		return this;
	};

	this.lookForChanges = function()
	{
		var enable = (this.original.kwText != this.current.kwText);
		this.menu.enableItem(this.idUse, enable);
		return this;
	};
}
