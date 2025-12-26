<style>
/* Footer Styles */
.footer {
    background-color: lightblue;
    padding-top: 60px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    padding: 0 20px 40px 20px;
}

.footer-column {
    display: flex;
    flex-direction: column;
}

/* Logo and Description */
.footer-logo {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    max-width: 100%;
}

.footer-logo img {
    width: 90%;
    max-width: 250px;
    height: auto;
}

.heart-icon {
    stroke: #0d9488;
    fill: none;
    margin-right: 8px;
}

.logo-text {
    font-size: 22px;
    font-weight: bold;
    color: black;
}

.footer-description {
    color: black;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 20px;
}

/* Social Media */
.social-media {
    display: flex;
    gap: 15px;
    margin-top: 5px;
}

.social-icon {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: black;
    border: 1px solid #0d9488;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.social-icon:hover {
    background-color: #0d9488;
    color: blue;
}

/* Column Titles */
.footer-title {
    font-size: 18px;
    font-weight: 600;
    color: black;
    margin-bottom: 20px;
}

/* Lists */
.footer-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.footer-icon {
    color: black;
    margin-right: 10px;
}

.footer-link {
    color: black;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-link:hover {
    color: #0d9488;
}

/* Get Started Button */
.get-started-button {
    display: inline-block;
    background-color: #D34DEE;
    color: black;
    font-weight: 600;
    text-decoration: none;
    padding: 10px 24px;
    border-radius: 15px;
    margin-bottom: 20px;
    text-align: center;
    transition: background-color 0.3s ease;
}

.get-started-button:hover {
    background-color: #D34DEE;
}

/* Responsive styling for the button */
@media (max-width: 768px) {
    .get-started-button {
        display: block;
        width: 100%;
        margin: 0 auto 20px auto;
    }
}
/* Newsletter */
.newsletter-form {
    display: flex;
    margin-top: 10px;
    margin-bottom: 20px;
}

.newsletter-input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #e2e8f0;
    border-right: none;
    border-radius: 4px 0 0 4px;
    outline: none;
    font-size: 14px;
}

.newsletter-button {
    background-color: #0d9488;
    color: white;
    border: none;
    padding: 0 15px;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.newsletter-button:hover {
    background-color: #0f766e;
}

/* Features */
.features-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    color: black;
    font-size: 14px;
}

.feature-icon {
    color: black;
    margin-right: 10px;
}

/* Copyright Section */
.copyright-section {
    background-color: darkblue;
    padding: 20px 0;
    border-top: 1px solid #e2e8f0;
}

.copyright-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
}

.copyright-text {
    color: white;
    font-size: 14px;
}

.footer-links {
    display: flex;
    gap: 20px;
}

.copyright-link {
    color: white;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.copyright-link:hover {
    color: white;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .footer-container {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    /* Create a services-links wrapper for mobile */
    .services-links-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    .copyright-container {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
    
    .newsletter-input {
        width: 100%;
        border-radius: 4px;
        border-right: 1px solid #e2e8f0;
        margin-bottom: 10px;
    }
    
    .newsletter-button {
        width: 100%;
        border-radius: 4px;
        padding: 12px 15px;
    }
    
    /* Center logo in mobile view */
    .footer-logo {
        justify-content: center;
        margin: 0 auto 15px auto;
    }
    
    .footer-logo img {
        max-width: 200px;
    }
    
    /* Center social media icons */
    .social-media {
        justify-content: center;
        margin: 10px auto;
    }
    
    /* Adjust font sizes for mobile */
    .footer-title {
        font-size: 16px;
    }
    
    .footer-description {
        text-align: center;
    }
}
</style>



<!-- Footer Section -->
<footer class="footer">
    <div class="footer-container">
        <!-- Logo and Description Section -->
        <div class="footer-column">
            <div class="footer-logo">
            <img src="../Admin/img/log.png" alt="Logo" style="width:90%;"> <!-- Add your logo image here -->
           </div>
            <p class="footer-description">Your trusted partner in health and wellness. Providing quality healthcare services across Sri Lanka.</p>
            
            <!-- Social Media Icons -->
            <center>
            <div class="social-media" style="margin-left:10px;">
                
    <a href="https://www.facebook.com/share/1BUFn5hKYY/" class="social-icon" target="_blank" rel="noopener noreferrer">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
        </svg>
    </a>
    <a href="https://wa.me/94777867942" class="social-icon" target="_blank" rel="noopener noreferrer">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
        </svg>
    </a>
    <a href="https://www.instagram.com/dailyhealthlk?igsh=MTBzYXljeHI5N3Rtdw==" class="social-icon" target="_blank" rel="noopener noreferrer">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
        </svg>
    </a>
    <a href="https://youtube.com/@dailyhealthlk?si=HusrGcS-FTdcg1eZ" class="social-icon" target="_blank" rel="noopener noreferrer">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
            <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
        </svg>
    </a>
</div>
</center>
        </div>
        
        
        <!-- Quick Links Column -->
        <div class="footer-column">
            <h3 class="footer-title">Quick Links</h3>
            <ul class="footer-list">
                <li class="footer-item">
                    <a href="Admin/display_messages.php" class="footer-link">Day's Thoughts</a>
                </li>
                <li class="footer-item">
                    <a href="Admin/winner_list.php" class="footer-link">Health Champs</a>
                </li>
                <li class="footer-item">
                    <a href="Admin/vid_display.php" class="footer-link">Health Talks</a>
                </li>
                <li class="footer-item">
                    <a href="Admin/download_list.php" class="footer-link">Downloads </a>
                </li>
                <li class="footer-item">
                    <a href="Admin/event_display.php" class="footer-link">Event</a>
                </li>
            </ul>
        </div>
        
     
       
        <!-- Health Updates Column -->
        <!-- Health Updates Column -->
<div class="footer-column">
    <h3 class="footer-title">Health Updates</h3>
    <p class="footer-description">Subscribe to receive health tips and updates.</p>
    
    <!-- Get Started Button -->
    <a href="https://wa.me/94777867942" class="get-started-button" target="_blank" rel="noopener noreferrer">
                 <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
        </svg>
        Send a meesage </a> 
    
    <!-- Features -->
    <ul class="features-list">
        <li class="feature-item">
            <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>Licensed Medical Professionals</span>
        </li>
        <li class="feature-item">
            <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span>24/7 Online Support</span>
        </li>
        <li class="feature-item">
            <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>Verified Health Information</span>
        </li>
    </ul>
</div>
    </div>
    
    <!-- Copyright Section -->
    <div class="copyright-section">
        <div class="copyright-container">
            <div class="copyright-text">© 2025 DailyHealth.lk. All rights reserved.</div>
            <div class="footer-links">
                <a href="#" class="copyright-link">Privacy Policy</a>
                <a href="#" class="copyright-link">Terms of Service</a>
                <a href="#" class="copyright-link">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

    <script>
function changeBigImage(image, title, description, role, date) {
    document.getElementById("bigEventImage").src = image;
    document.getElementById("bigEventTitle").innerText = title;
    document.getElementById("bigEventDescription").innerText = description;
    document.getElementById("bigEventRole").innerText = role;
    document.getElementById("bigEventDate").innerText = date;
}
// Toggle the full description visibility
    function toggleDescription(id) {
        var descriptionElement = document.getElementById('description-' + id);
        
        var fullDescription = '<?php echo addslashes($description); ?>';
        
        if (descriptionElement.innerHTML.includes('Read More')) {
            descriptionElement.innerHTML = fullDescription + ' <span class="read-more" onclick="toggleDescription(' + id + ')">Read Less</span>';
        } else {
            var shortDescription = fullDescription.length > 50 ? fullDescription.substring(0, 50) + '...' : fullDescription;
            descriptionElement.innerHTML = shortDescription + ' <span class="read-more" onclick="toggleDescription(' + id + ')">Read More</span>';
        }
    }
    
   document.querySelector('.navbar a[href="#events"]').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default anchor behavior

    // Get the height of the navbar (adjust this value if necessary)
    const navbarHeight = document.querySelector('.navbar').offsetHeight;

    // Scroll to the target element with the appropriate offset
    window.scrollTo({
        top: document.getElementById('events').offsetTop - navbarHeight,
        behavior: 'smooth' // Smooth scrolling
    });
});
         document.querySelector('.navbar a[href="#downloads"]').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default anchor behavior

    // Get the height of the navbar (adjust this value if necessary)
    const navbarHeight = document.querySelector('.navbar').offsetHeight;

    // Scroll to the target element with the appropriate offset
    window.scrollTo({
        top: document.getElementById('downloads').offsetTop - navbarHeight,
        behavior: 'smooth' // Smooth scrolling
    });
});
        document.querySelector('.navbar a[href="#videos"]').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default anchor behavior

    // Get the height of the navbar (adjust this value if necessary)
    const navbarHeight = document.querySelector('.navbar').offsetHeight;

    // Scroll to the target element with the appropriate offset
    window.scrollTo({
        top: document.getElementById('videos').offsetTop - navbarHeight,
        behavior: 'smooth' // Smooth scrolling
    });
});
               document.querySelector('.navbar a[href="#calendar"]').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default anchor behavior

    // Get the height of the navbar (adjust this value if necessary)
    const navbarHeight = document.querySelector('.navbar').offsetHeight;

    // Scroll to the target element with the appropriate offset
    window.scrollTo({
        top: document.getElementById('calendar').offsetTop - navbarHeight,
        behavior: 'smooth' // Smooth scrolling
    });
});
        document.querySelector('.navbar a[href="#Daily_Messages"]').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default anchor behavior

    // Get the height of the navbar (adjust this value if necessary)
    const navbarHeight = document.querySelector('.navbar').offsetHeight;

    // Scroll to the target element with the appropriate offset
    window.scrollTo({
        top: document.getElementById('Daily_Messages').offsetTop - navbarHeight,
        behavior: 'smooth' // Smooth scrolling
    });
});

document.querySelector('.navbar a[href="#winners"]').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default anchor behavior

    // Get the height of the navbar (adjust this value if necessary)
    const navbarHeight = document.querySelector('.navbar').offsetHeight;

    // Scroll to the target element with the appropriate offset
    window.scrollTo({
        top: document.getElementById('winners').offsetTop - navbarHeight,
        behavior: 'smooth' // Smooth scrolling
    });
});
        document.addEventListener('DOMContentLoaded', function () {
            const cells = document.querySelectorAll('td');
            const dateModal = new bootstrap.Modal(document.getElementById('dateModal')); // Initialize the modal

            cells.forEach(cell => {
                cell.addEventListener('click', function () {
                    const date = this.getAttribute('data-date');  // Get the clicked date
                    const title = this.getAttribute('data-title');
                    const description = this.getAttribute('data-description');

                    // Set the modal title and daily message (if any)
                    document.getElementById('dateModalLabel').innerText = 'Details for ' + date;
                    document.getElementById('dailyMessage').innerText = title || "No daily message for this day.";
                    document.getElementById('noteMessage').innerText = description || "No description available.";

                    // Fetch existing note for this date
                    fetchNote(date);

                    // Show the modal
                    dateModal.show();
                });
            });

            function fetchNote(date) {
                const year = <?php echo $selectedYear; ?>;
                const month = <?php echo $selectedMonth; ?>;

                // Get the selected cell's existing note
                const selectedCell = document.querySelector(`td[data-date='${date}']`);
                if (selectedCell && selectedCell.getAttribute('data-description')) {
                    const existingNote = selectedCell.getAttribute('data-description');
                    document.getElementById('noteMessage').innerText = existingNote;
                    
                    // Show Edit and Delete buttons
                    document.getElementById('editNoteButton').style.display = 'inline-block';
                    document.getElementById('deleteNoteButton').style.display = 'inline-block';
                    document.getElementById('addNoteButton').style.display = 'none';
                    return; // Avoid unnecessary AJAX request
                }

              // If no existing note in frontend, fetch from backend
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `Admin/fetch_note.php?year=${year}&month=${month}&date=${date}`, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === "success") {
                document.getElementById('noteMessage').innerText = response.note || "No note available.";
                if (selectedCell) selectedCell.setAttribute('data-description', response.note); // Store it in the cell
                
                document.getElementById('editNoteButton').style.display = 'inline-block';
                document.getElementById('deleteNoteButton').style.display = 'inline-block';
                document.getElementById('addNoteButton').style.display = 'none';
            } else {
                document.getElementById('noteMessage').innerText = "No note available.";
                document.getElementById('editNoteButton').style.display = 'none';
                document.getElementById('deleteNoteButton').style.display = 'none';
                document.getElementById('addNoteButton').style.display = 'inline-block';
            }
        }
    };
    xhr.send();
}



    // Handle "Add Note" button click
    document.getElementById('addNoteButton').addEventListener('click', function() {
        const date = document.getElementById('dateModalLabel').innerText.split(' ')[2]; // Extract date from modal title
        const note = prompt("Enter your note for this day:");
        if (note) {
            saveNote(date, note);
        }
    });

    // Handle "Edit Note" button click
    document.getElementById('editNoteButton').addEventListener('click', function() {
        const date = document.getElementById('dateModalLabel').innerText.split(' ')[2]; // Extract date from modal title
        const currentNote = document.getElementById('noteMessage').innerText;
        const updatedNote = prompt("Edit your note for this day:", currentNote);
        if (updatedNote !== null) {
            saveNote(date, updatedNote);
        }
    });

    // Handle "Delete Note" button click
    document.getElementById('deleteNoteButton').addEventListener('click', function() {
        const date = document.getElementById('dateModalLabel').innerText.split(' ')[2]; // Extract date from modal title
        if (confirm("Are you sure you want to delete this note?")) {
            deleteNote(date);
        }
    });

    function saveNote(date, note) {
    const year = <?php echo $selectedYear; ?>;
    const month = <?php echo $selectedMonth; ?>;

    const noteData = {
        year: year,
        month: month,
        date: date,
        note: note
    };

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Admin/save_note.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === "success") {
                alert("Note saved successfully!");
                
                // Update the modal content
                document.getElementById('noteMessage').innerText = response.note;

                // Update the clicked date cell to store the new note
                const selectedCell = document.querySelector(`td[data-date='${date}']`);
                if (selectedCell) {
                    selectedCell.setAttribute('data-description', response.note);
                }
                
                fetchNote(date); // Refresh the note display
            } else {
                alert("Error saving note: " + response.message);
            }
        }
    };
    xhr.send(JSON.stringify(noteData));
}



    // Function to delete a note
function deleteNote(date) {
    const year = <?php echo json_encode($selectedYear); ?>;
    const month = <?php echo json_encode($selectedMonth); ?>;

    const noteData = { year, month, date };

    // Create an AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "Admin/delete_note.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === "success") {
                        alert("Note deleted successfully!");
                        document.getElementById('noteMessage').innerText = "No note available.";
                        fetchNote(date); // Refresh the note display
                        location.reload(); // Refresh the page after successful deletion
                    } else {
                        alert("Error deleting note: " + response.message);
                    }
                } catch (error) {
                    console.error("JSON Parse Error:", error);
                    alert("An unexpected error occurred.");
                }
            } else {
                console.error("AJAX Error: ", xhr.statusText);
                alert("Failed to communicate with the server.");
            }
        }
    };

    xhr.send(JSON.stringify(noteData));
}

});

//like button
 function likePost(id) {
            fetch('Admin/like_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    document.getElementById("like-count-" + id).innerText = data.likes;
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
        }


function shareMessage(postId) {
    const shareUrl = 'https://www.dailyhealth.lk/Admin/display_messages.php?id=' + postId;
    const shareText = 'Check out this post!';

    if (navigator.share) {
        navigator.share({
            title: 'Share this message',
            text: shareText,
            url: shareUrl
        }).then(() => {
            console.log('Thanks for sharing!');
        }).catch((error) => {
            console.error('Error sharing:', error);
        });
    } else {
        // Fallback for browsers that do not support the Web Share API
        const shareWindow = window.open('', '_blank', 'width=600,height=400');
        shareWindow.document.write(`
            <div>
                <h3>Share this message</h3>
                <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}" target="_blank">Facebook</a><br>
                <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}" target="_blank">Twitter</a><br>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(shareUrl)}&title=${encodeURIComponent(shareText)}" target="_blank">LinkedIn</a><br>
            </div>
        `);
    }
}

</script>