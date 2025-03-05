document.getElementById("commentForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const blogId = document.getElementById("blogId").value;
    const comment = document.getElementById("commentText").value;

    fetch("?url=blog/comment", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `blog_id=${blogId}&comment=${encodeURIComponent(comment)}`
    }).then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                location.reload();
            }
        });
});