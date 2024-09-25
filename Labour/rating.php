<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labour Rating</title>

    <style>
        /* Styling for the rating form */
        #rating-form {
            display: flex;
            flex-direction: column;
            width: 300px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        #rating-form label {
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }

        #rating-form select, #rating-form textarea {
            margin-bottom: 15px;
            padding: 8px;
            font-size: 14px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 10px;
            background-color: green;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: darkgreen;
        }

        /* Styling for displaying reviews */
        .review {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: #f5f5f5;
        }

        .review strong {
            font-weight: bold;
            color: #333;
        }

        #average-rating {
            text-align: center;
            margin: 20px;
        }

        /* Basic container for reviews */
        #reviews {
            width: 50%;
            margin: 0 auto;
            padding: 10px;
        }
    </style>
</head>
<body>

    <!-- Display average rating -->
    <div id="average-rating">
        <h3>Average Rating: <!-- This will be dynamically filled by backend PHP --></h3>
    </div>

    <!-- Rating form -->
    <form action="rate_labour.php" method="POST" id="rating-form">
        <input type="hidden" name="labour_id" value="<?php echo $labour_id; ?>">

        <label for="rating">Rate this labourer:</label>
        <select name="rating" id="rating">
            <option value="1">1 Star</option>
            <option value="2">2 Stars</option>
            <option value="3">3 Stars</option>
            <option value="4">4 Stars</option>
            <option value="5">5 Stars</option>
        </select>

        <label for="review">Write a review (optional):</label>
        <textarea name="review" id="review" rows="4" placeholder="Share your experience..."></textarea>

        <button type="submit">Submit Rating</button>
    </form>

    <!-- Display section for reviews -->
    <div id="reviews">
        <!-- Dynamically filled by backend PHP to show reviews -->
    </div>

    <script>
        // Optional: Handle form submission via AJAX without refreshing the page
        document.getElementById('rating-form').addEventListener('submit', function(e) {
            e.preventDefault();  // Prevent the default form submission

            // Create FormData object from the form
            let formData = new FormData(this);

            // Send the form data using Fetch API
            fetch('rate_labour.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data);  // Show response message from backend
                location.reload();  // Reload the page to show updated reviews and ratings
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>

</body>
</html>
