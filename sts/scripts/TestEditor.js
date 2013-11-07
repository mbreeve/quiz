//
// TestEditor.js: manages the HTML for the Update Test Form
// Project: STS - Specialised Test Setter
//

function TestEditor(parent)
{
	this.parent = parent;
	this.root = parent.root;
	this.launch = this.root.launch;

	this.menu = new MainMenu(this.root, "testEditor");
	this.menu.setFixed();
	this.menu.hide();

	this.root.$divForm.append(
		this.$divOuter = $("<div id='testEditor'></div>").hide().append(
			$("<table border='true' class='stand'></table>").append(
				$("<tbody></tbody>").append(
					$("<tr></tr>").append(
						$("<td class='labels'></td>").append(
							$("<label for='ident'>Identification</label>")),
						$("<td class='inputs'></td>").append(
							this.$ident = $("<input autofocus size='80' />")
								.attr("placeholder", "A unique identifier"))),
					$("<tr></tr>").append(
						$("<td class='labels'></td>").append(
							$("<label for='descr'>Description</label>")),
						$("<td class='inputs'></td>").append(
							this.$descr = $("<input size='80' />")
								.attr("placeholder", "An optional description"))),
					$("<tr></tr>").append(
						$("<td class='labels'></td>").append(
							$("<label for='kwText'>Keywords</label>")),
						$("<td class='kwText'></td>").append(
							this.$kwText = $("<input size='80' />")
								.attr("placeholder", "Keywords separated by semi-colons"))),
					this.$kwList = $("<tr></tr>").hide().append(
						$("<td class='labels'></td>").append(
							$("<label for='cboxes'>Check Boxes</label>")),
						$("<td></td>").append(
							this.$cboxes = $("<div class='kwlist' id='cboxes'></div>")))))));

	var thisObj = this;
	this.$divOuter.on("click", ".kwText", function()
	{
		thisObj.showKwList();
	});
	this.$divOuter.on("keyup", ".inputs", function()
	{
		thisObj.syncDisplay(true);
		thisObj.lookForChanges();
	});
	this.$divOuter.on("change", ".cbox", function()
	{
		thisObj.deriveKeywords();
		thisObj.showCboxes();
		thisObj.syncDisplay(false);
		thisObj.lookForChanges();
	});

	// Set the kwList state to hidden as an initial state.
	this.showKwList = function(onNotOff)
	{
		this.kwListShowing = !this.kwListShowing;
		if (onNotOff === true || onNotOff === false)
		{
			this.kwListShowing = onNotOff;
		}
		if (this.kwListShowing)
		{
			this.$kwList.show();
		}
		else
		{
			this.$kwList.hide();
		}
	};

	this.syncDisplay = function(fromScreen)
	{
		if (fromScreen)		// syncDisplay(true)
		{
			this.test.fields.ident = this.$ident.val();
			this.test.fields.descr = this.$descr.val();
		}
		else							// syncDisplay(false)
		{
			this.$ident.val(this.test.fields.ident);
			this.$descr.val(this.test.fields.descr);
			this.$kwText.val(this.test.fields.kwText);
		}
		return this;
	};

	// Create a new test, or view/edit an existing one, for the given setter.
	this.edit = function(setter, test)
	{
		this.showKwList(false);
		this.setter = setter;
		this.test = test;
		this.test.original = $.extend(true, { }, test.fields)
		this.creating = this.test.dbid == 0;
		this.showCboxes();
		this.syncDisplay(false);
		var greeting = this.creating ? "Create Test" : "View/Edit Tests";
		this.root.whiteBoard.setGreeting(greeting);
		this.$divOuter.show();
		this.showOptions();
		return this;
	};

	this.delete = function(setter, test)
	{
		if (confirm("Do you want to delete " + test.fields.ident + "?"))
		{
			this.launch.postAjax(
			{
				class: "TestsManager",
				method: "deleteTest",
				args:
				{
					idSetter: setter.fields.idSetter,
					idTest: test.fields.idTest
				}
			});
		}
		this.finish().parent.resume();
		return this;
	};

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
		var ident = this.creating ? " New Test" : " <" + this.test.fields.ident + ">";
		this.idSave = this.menu.addItem("Save" + ident, false, function()
		{
			thisObj.save().finish().parent.resume();
		});
		this.idAbandon = this.menu.addItem("Abandon" + ident, false, function()
		{
			thisObj.abandon().finish().parent.resume();
		});
		this.idList = this.menu.addItem("Back to <List Tests>", true, function()
		{
			thisObj.finish().parent.resume();
		});
		this.menu.show();
		return this;
	};

	this.abandon = function()
	{
		this.test.fields.keywords = { };
		$.extend(true, this.test.fields, this.test.original);
		return this;
	};

	this.save = function()
	{
		this.launch.postAjax(
		{
			class: "TestsManager",
			method: this.creating ? "createTest" : "updateTest",
			args:
			{
				idSetter: this.setter.dbid,
				idTest: this.test.fields.idTest,
				ident: this.test.fields.ident,
				descr: this.test.fields.descr,
				keywords: this.test.fields.keywords
			}
		});
		if (this.creating)
		{
			this.test.fields.keywords = { };
			$.extend(true, this.test.fields, this.test.original);
			this.creating = false;
		}
		else
		{
			this.test.original = { };
			$.extend(true, this.test.original, this.test.fields);
			this.lookForChanges();
		}
		return this;
	};

	this.lookForChanges = function()
	{
		var enable = this.test.fields.ident.length > 0 &&
			(this.creating ||
			this.test.original.ident != this.test.fields.ident ||
			this.test.original.descr != this.test.fields.descr ||
			this.test.original.kwText != this.test.fields.kwText);

		this.menu.enableItem(this.idSave, enable);
		this.menu.enableItem(this.idAbandon, enable);
		this.menu.enableItem(this.idList, !enable);
		return this;
	};

	// Take the keywords data for this test, i.e. (only) those keywords which are required
	// (have been previously checked), and merge with the set of all keywords, i.e.
	// including all those that aren't required for this particular test. From the result,
	// produce a new set of checkboxes.
	this.showCboxes = function()
	{
		// The $.extend() merges together all test keywords, including new ones,
		// to the names in setter.keywords (the original set).
		var newKeywords = { };
		$.extend(newKeywords, this.setter.keywords, this.test.fields.keywords);

		var $curRow = $("<ul></ul>");
		this.$cboxes.empty().append($curRow);
		var thisObj = this;
		var count = 0;
		var keywords = this.test.fields.keywords;
		$.each(newKeywords, function(key, ident)
		{
			$curRow.append(
				$("<li class='keyword'></li>").append(
					$("<input type='checkbox' class='cbox' />").prop("checked", keywords[key]),
					$("<input type='text' class='kwinput' />").val(ident).prop("disabled", true)));
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
		var allKeywords = this.setter.keywords;

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
		this.test.fields.keywords = keywords;
		return this;
	};
}
