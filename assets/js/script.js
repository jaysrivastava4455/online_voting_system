
$(document).ready(function () {
    $('#voteForm input[name="candidate_id"]').on('change', function () {
        // First, remove 'selected' class from all cards
        $('.candidate-card').removeClass('selected');

        // Then, add 'selected' class to the parent of the checked radio button
        if ($(this).is(':checked')) {
            $(this).closest('.candidate-card').addClass('selected');
        }
    });
});
