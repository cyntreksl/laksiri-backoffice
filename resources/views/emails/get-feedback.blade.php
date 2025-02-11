<style>
    body {
        background-color: #f8fafc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        text-align: center;
        padding: 20px;
    }

    .feedback-mail {
        background-color: #fff;
        max-width: 500px;
        margin: 20px auto;
        padding: 30px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        transition: box-shadow 0.3s ease;
    }

    .feedback-mail:hover {
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .feedback-mail h1 {
        margin-bottom: 20px;
        color: #333;
    }

    .feedback-mail h4 {
        color: #6c757d;
        margin-bottom: 20px;
        font-weight: normal;
    }

    .feedback-mail img {
        width: 50px;
        height: auto;
        margin-bottom: 10px;
    }

    .submit-btn {
        background-color: #007bff;
        color: #fff;
        padding: 12px 20px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        width: 100%;
        max-width: 150px;
        margin: 0 auto;
        text-decoration: none;
    }
</style>

<div>
    <div class="feedback-mail">
        <!-- Logo goes here -->
        <img class="logo" style="width: 30%;"
            src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/app-logo.png'))) }}"
            alt="app_logo">

        <h1>Hi,</h1>
        <h4>Thanks for choosing Laksiri! Please tell us your experience in this 30-second survey.Your
            feedback help us to create a better experience for you and for all of our customers</h4>

        <a href="{{ $feedbackURL }}" class="submit-btn">Tell us how it went</a>
    </div>
</div>
