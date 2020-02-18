define([''], function(){
    return {
        init: function(answered) {
            var selects = document.querySelectorAll('*[id^="id_questions"]');

            // Event listener function.
            var eventfunction = function() {
                var values = [];
                // Get all currently selected elements.
                selects.forEach(function(select) {
                    values.push(select.selectedIndex);
                });

                // For each select, disable controls that are selected in other selects.
                selects.forEach(function(select) {
                    for (var i = 0; i < select.options.length; i++) {
                        var option = select.options[i];
                        if (option.index !== select.selectedIndex && (values.includes(option.index)
                            || answered.includes(option.value))) {
                            option.style.display = 'none';
                        } else {
                            option.style.display = '';
                        }
                    }
                });
            };

            // Bind to change and load events.
            selects.forEach(function(select) {
                select.addEventListener('change', eventfunction());
                select.addEventListener('load', eventfunction());
            });
        }
    };
});
