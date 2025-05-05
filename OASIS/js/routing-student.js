const homeButton = document.getElementById('home-button');
const profileButton = document.getElementById('profile-button');

// Navigate to Home
homeButton.onclick = function() {
    window.location.href = 'student-home.php'; // Same directory, no prefix needed
};

// Navigate to User Profile
profileButton.onclick = function() {
    window.location.href = 'user-profile.php'; // Same directory
};