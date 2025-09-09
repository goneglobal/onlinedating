$(document).ready(function() {
    $.get('../src/app.php', function(data) {
        $('#user-list').empty();
        data.forEach(function(user) {
            $('#user-list').append(
                `<div class="user-card">
                    <h2>${user.name}, ${user.age}</h2>
                    <p>${user.bio}</p>
                </div>`
            );
        });
    });
});
