define([''], function(){
    return {
        init: function() {
            var selects = document.querySelectorAll('*[id^="id_questions"]');

            selects.forEach(function(select) {
                select.addEventListener('change', function () {
                    var values = [];
                    // Get all currently selected elements.
                    selects.forEach(function(select) {
                        values.push(select.selectedIndex);
                    });

                    // For each select, disable controls that are selected in other selects.
                    selects.forEach(function(select) {
                        for (var i = 0; i < select.options.length; i++) {
                            var option = select.options[i];
                            if (option.index !== select.selectedIndex && values.includes(option.index)) {
                                option.style.display = 'none';
                            } else {
                                option.style.display = '';
                            }
                        }
                    });
                });
            });
        }
    };
});
