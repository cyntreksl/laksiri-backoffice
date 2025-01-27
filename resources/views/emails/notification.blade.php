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

    .signature {
        margin-top: 30px;
        font-size: 16px;
        color: #333;
    }
</style>

<div>
    <div class="feedback-mail">
        <!-- Logo goes here -->
        <img class="logo" style="width: 30%;"
             src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/app-logo.png'))) }}"
             alt="app_logo">

        <h1>{{ $customer ? 'Dear '.$customer : 'Hi'}},</h1>
        <h4>{{ $success_message ?? ''}}</h4>
        <h4>{{ $detail_message ?? ''}}</h4>

        <div class="signature">
            Regards,<br>
            <strong>Laksiri Seva Cargo</strong>
        </div>
    </div>
</div>
