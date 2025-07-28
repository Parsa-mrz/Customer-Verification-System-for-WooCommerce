(function ($) {
  "use strict";

  $(document).ready(function () {
    //Switch tabs.
    const $tabLinks = $(".tab-link");
    const $tabContents = $(".tab-content");

    $tabLinks.on("click", function () {
      $tabLinks.removeClass("active");
      $(this).addClass("active");

      $tabContents.removeClass("active");
      $("#" + $(this).data("tab")).addClass("active");
    });
    //hide alert message.
    $(".message[data-timeout]").each(function () {
      var $el = $(this);
      var timeout = parseInt($el.data("timeout"), 10) * 1000;

      setTimeout(function () {
        $el.fadeOut(600, function () {
          $el.remove();
        });
      }, timeout);
    });

    $(".verify-woo-dropdown").each(function () {
      const $dropdown = $(this);
      const $toggle = $dropdown.find(".dropdown-toggle");
      const $optionsContainer = $dropdown.find(".dropdown-options");
      const $hiddenSelect = $dropdown.find("select");
      const $currentValueSpan = $dropdown.find(".current-value");

      // Set initial selected option in the custom UI
      const initialValue = $hiddenSelect.val();
      let initialLabel = "";
      if (initialValue) {
        initialLabel = $hiddenSelect
          .find('option[value="' + initialValue + '"]')
          .text();
      } else {
        // Fallback: If no value is set, use the text of the first custom option
        initialLabel = $optionsContainer
          .find(".dropdown-option")
          .first()
          .text();
      }
      $currentValueSpan.text(initialLabel);

      // Highlight initial selected option in the custom options list
      // This ensures the current selection is visually marked when the dropdown opens
      $optionsContainer.find(".dropdown-option").each(function () {
        if ($(this).data("value") === initialValue) {
          $(this).addClass("selected");
        }
      });

      // Toggle dropdown open/close on click of the custom "toggle" area
      $toggle.on("click", function () {
        // Close any other open dropdowns first (optional, but good UX)
        $(".verify-woo-dropdown .dropdown-options.active")
          .not($optionsContainer)
          .removeClass("active");
        $(".verify-woo-dropdown .dropdown-toggle.active")
          .not($toggle)
          .removeClass("active");

        $optionsContainer.toggleClass("active");
        $toggle.toggleClass("active");
      });

      // Handle selection of an option from the custom list
      $optionsContainer.find(".dropdown-option").on("click", function () {
        const selectedValue = $(this).data("value"); // Get the value from data-value attribute
        const selectedLabel = $(this).text(); // Get the display text

        // Update the hidden native <select> element's value
        $hiddenSelect.val(selectedValue);

        // Update the displayed text in the custom UI
        $currentValueSpan.text(selectedLabel);

        // Update 'selected' class for visual feedback in the custom options list
        $optionsContainer.find(".dropdown-option").removeClass("selected"); // Remove from all
        $(this).addClass("selected"); // Add to the clicked one

        // Close the dropdown after selection
        $optionsContainer.removeClass("active");
        $toggle.removeClass("active");

        // Trigger a change event on the hidden <select>. This is important
        // if any other WordPress scripts or your own logic relies on the
        // native 'change' event of the select element.
        $hiddenSelect.trigger("change");
      });

      // Close dropdown when clicking anywhere outside of it
      $(document).on("click", function (event) {
        // Check if the click was outside the current dropdown
        if (
          !$dropdown.is(event.target) &&
          $dropdown.has(event.target).length === 0
        ) {
          $optionsContainer.removeClass("active");
          $toggle.removeClass("active");
        }
      });
    });
  });
})(jQuery);
