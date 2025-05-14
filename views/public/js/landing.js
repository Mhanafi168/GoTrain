document.addEventListener('DOMContentLoaded', function() {
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Typing effect for the hook text
    const text = "Your Journey Begins With A Click";
    const typingText = document.getElementById('typingText');
    typingText.innerHTML = "";
    let i = 0;

    function typeWriter() {
        if (i < text.length) {
            typingText.innerHTML += text.charAt(i);
            i++;
            setTimeout(typeWriter, 100);
        }
    }

    typeWriter();

    // Smooth scroll when clicking on scroll down arrow
    document.getElementById('scrollDown').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('#features').scrollIntoView({
            behavior: 'smooth'
        });
    });

    // Show search form when clicking on book button
    document.getElementById('bookButton').addEventListener('click', function(e) {
        e.preventDefault();
        const searchForm = document.getElementById('searchForm');
        searchForm.classList.toggle('active');

        // Set minimum date to today for departure date input
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('departDate').setAttribute('min', today);
        document.getElementById('departDate').value = today;
    });

    // Search button functionality with simple validation
    document.getElementById('searchButton').addEventListener('click', function() {
        const from = document.getElementById('fromStation').value;
        const to = document.getElementById('toStation').value;

        if (from && to) {
            // Simulate search with countdown
            const countdownElement = document.getElementById('countdown');
            let seconds = 3;

            countdownElement.innerHTML = `Searching for trains... ${seconds}`;

            const interval = setInterval(function() {
                seconds--;
                countdownElement.innerHTML = `Searching for trains... ${seconds}`;

                if (seconds <= 0) {
                    clearInterval(interval);
                    countdownElement.innerHTML = "Search complete! Redirecting to results...";

                    // In a real app, this would redirect to results page
                    setTimeout(function() {
                        alert("This is a demo. In a real application, you would be redirected to search results.");
                        countdownElement.innerHTML = "";
                    }, 2000);
                }
            }, 1000);
        } else {
            alert("Please enter both origin and destination stations.");
        }
    });



    // Add hover effect to feature boxes
    const featureBoxes = document.querySelectorAll('.feature-box');
    featureBoxes.forEach(box => {
        box.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
            this.style.transition = 'transform 0.3s ease';
        });

        box.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});