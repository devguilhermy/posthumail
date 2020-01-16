
$(document).ready(function(){
    $('.search-field').on("keyup input", function(){
        /* Get input value on change */
        var inputVal = $(this).val();
        var tableSearch = $('#table').val();
        var fieldSearch = $('#field').val();
        var resultDropdown = $('#display');

        if(inputVal.length){
            $.ajax({
                url: "https://localhost/exub/php/backend-search.php",
                data: {term: inputVal, table: tableSearch, field: fieldSearch},
                success: function(data){
                    // Display the returned data in browser
                    resultDropdown.html(data);
                },
                dataType: "html"
              });
        } else{
            resultDropdown.empty();
        }
    });

    // Set search input value on click of result item
    $(document).on("click", "#display li", function(){
        $('.search-field').val($(this).text());
        $('.display').empty();
    });
});
