$(document).ready(function() {
    // Initialize counter animation
    $('.counter').counterUp({
        delay: 10,
        time: 1000
    });

    // Toggle sidebar on mobile
    $('.sidebar-toggle').click(function() {
        $('.sidebar').toggleClass('show');
    });

    // Close sidebar when clicking outside on mobile
    $(document).click(function(event) {
        var $target = $(event.target);
        if (!$target.closest('.sidebar').length &&
            !$target.closest('.sidebar-toggle').length &&
            $('.sidebar').hasClass('show')) {
            $('.sidebar').removeClass('show');
        }
    });

    // Animate elements when they come into view
    function animateOnScroll() {
        $('.card, .booking-item, .quick-action').each(function() {
            var position = $(this).offset().top;
            var scroll = $(window).scrollTop();
            var windowHeight = $(window).height();

            if (scroll + windowHeight > position) {
                $(this).css('opacity', '1');
                $(this).css('transform', 'translateY(0)');
            }
        });
    }

    $(window).scroll(function() {
        animateOnScroll();
    });

    animateOnScroll(); // Trigger on page load

    // Quick action clicks
    $('.quick-action').click(function() {
        var action = $(this).find('p').text().trim();

        switch (action) {
            case 'Book Ticket':
                window.location.href = 'book-ticket.php';
                break;
            case 'Print Ticket':
                window.location.href = 'view-tickets.php';
                break;
            case 'Train Timings':
                window.location.href = 'train-timings.php';
                break;
            case 'Booking History':
                window.location.href = 'view-tickets.php';
                break;
            default:
                break;
        }
    });

    // Apply button hover effects
    $('.btn').hover(
        function() {
            $(this).addClass('pulse');
        },
        function() {
            $(this).removeClass('pulse');
        }
    );

    // View ticket buttons
    $('.btn-primary').click(function() {
        // If it's a "View" button for tickets
        if ($(this).text().trim() === 'View') {
            // Get the booking ID
            var bookingId = $(this).closest('tr').find('td:first').text();
            // This would redirect to ticket detail page in a real implementation
            alert('Viewing ticket: ' + bookingId);
        }
    });

    // Cancel ticket buttons
    $('.btn-outline-danger').click(function() {
        // Confirm before cancellation
        if (confirm('Are you sure you want to cancel this ticket?')) {
            // In a real implementation, this would make an AJAX call to cancel the ticket
            alert('Ticket cancellation request sent!');
        }
    });

    // Recharge button
    $('.balance-card .btn-light').click(function() {
        // This would redirect to recharge page in a real implementation
        alert('Redirecting to recharge page...');
    });

    // Book new ticket button
    $('.welcome-text .btn-primary').click(function() {
        // This would redirect to booking page in a real implementation
        alert('Redirecting to ticket booking page...');
    });
});