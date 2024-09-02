<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import logo from "../../../../images/logo_main.png";

const urlParams = new URLSearchParams(window.location.search);
const userId = urlParams.get("user");
const hblId = urlParams.get("hbl");
const token = urlParams.get("token");
const errorMessage = ref("");
const isSuccess = ref(false);
const form = useForm({
    userId: userId,
    hblId: hblId,
    tokenId: token,
    rating: 0,
    comment: null,
});

const handleSubmit = () => {
    errorMessage.value = "";
    isSuccess.value = false;

    if (!form.rating && !form.comment) {
        errorMessage.value = "Please enter rating or a comment!";
        return;
    }

    form.post(route("feedback.upload"), {
        onSuccess: () => {
            isSuccess.value = true;
            form.rating = 0;
            form.comment = null;
            push.success("Feedback sent Successfully!");
            form.reset();
        },
        onError: () => console.log("error"),
        onFinish: () => console.log("finish"),
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <div>
        <div class="feedback-form">
            <!-- Logo goes here -->
            <div>
                <img :src="logo" alt="logo" class="mx-auto size-64" />
            </div>

            <h2>Rate your experience</h2>
            <h4>
                We highly value your feedback! Kindly take a moment to rate your
                experience and provide us with your valueble feedback.
            </h4>

            <!-- Success message -->
            <div v-if="isSuccess" class="success-message">
                <p>
                    Thank you for your feedback! Your response has been
                    recorded.
                </p>
            </div>

            <!--Start of the form -->
            <form @submit.prevent="handleSubmit">
                <div class="rating">
                    <input
                        type="radio"
                        id="star5"
                        name="rating"
                        value="5"
                        v-model="form.rating"
                    />
                    <label for="star5">&#9733;</label>
                    <input
                        type="radio"
                        id="star4"
                        name="rating"
                        value="4"
                        v-model="form.rating"
                    />
                    <label for="star4">&#9733;</label>
                    <input
                        type="radio"
                        id="star3"
                        name="rating"
                        value="3"
                        v-model="form.rating"
                    />
                    <label for="star3">&#9733;</label>
                    <input
                        type="radio"
                        id="star2"
                        name="rating"
                        value="2"
                        v-model="form.rating"
                    />
                    <label for="star2">&#9733;</label>
                    <input
                        type="radio"
                        id="star1"
                        name="rating"
                        value="1"
                        v-model="form.rating"
                    />
                    <label for="star1">&#9733;</label>
                </div>
                <div class="comment">
                    <label for="comment">Tell us more:</label>
                    <textarea
                        v-model="form.comment"
                        name="comment"
                        placeholder="Write your feedback here..."
                    ></textarea>
                </div>
                <div
                    v-if="errorMessage"
                    class="mb-3 alert flex overflow-hidden rounded-lg bg-error/10 text-error dark:bg-error/15"
                >
                    <div class="flex flex-1 items-center space-x-3 p-4">
                        <svg
                            class="size-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                            />
                        </svg>
                        <div class="flex-1">{{ errorMessage }}</div>
                    </div>

                    <div class="w-1.5 bg-error"></div>
                </div>
                <button type="submit" class="submit-btn">Submit</button>
            </form>
        </div>
    </div>
</template>
<style>
body {
    background-color: #f8fafc;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    text-align: center;
    padding: 20px;
}

.logo-img {
    max-width: 40%; /* Increase the max width */
    height: auto;
    margin-bottom: 20px; /* Optional: add some spacing */
    transition: transform 0.3s ease; /* Optional: smooth resizing effect */
}

.success-message {
    background-color: #f8fafc;
    color: #007bff;
    border: 1px solid #c3e6cb;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-weight: bold;
    text-align: center;
}

.feedback-form {
    background-color: #fff;
    max-width: 500px;
    margin: 20px auto;
    padding: 30px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    transition: box-shadow 0.3s ease;
}

.feedback-form:hover {
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.feedback-form h2 {
    margin-bottom: 20px;
    color: #333;
}

.feedback-form h4 {
    color: #6c757d;
    margin-bottom: 20px;
    font-weight: normal;
}

.feedback-form img {
    width: 50px;
    height: auto;
    margin-bottom: 10px;
}

.rating {
    margin-bottom: 20px;
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
}

.rating input {
    display: none;
}

.rating label {
    font-size: 32px;
    cursor: pointer;
    color: #ddd;
    transition: color 0.2s;
}

.rating label:hover,
.rating label:hover ~ label {
    color: #ffa500;
}

.rating input:checked ~ label {
    color: #ffa500;
}

.comment {
    margin-bottom: 20px;
    text-align: left;
}

.comment label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.comment textarea {
    width: 100%;
    height: 100px;
    resize: none;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    outline: none;
    transition: border-color 0.2s;
}

.comment textarea:focus {
    border-color: #007bff;
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
}

.submit-btn:hover {
    background-color: #0056b3;
}

@media (max-width: 600px) {
    .feedback-form {
        padding: 20px;
        margin: 10px;
    }

    .rating label {
        font-size: 28px;
    }

    .comment textarea {
        height: 80px;
    }

    .submit-btn {
        max-width: 100%;
    }
}
</style>
