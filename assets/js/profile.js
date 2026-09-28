document.addEventListener("DOMContentLoaded", function () {

    const editModal = document.getElementById("editModal");

    const openModalButton =
        document.getElementById("openModalButton");

    const closeModalButton =
        document.getElementById("closeModalButton");

    const cancelButton =
        document.getElementById("cancelButton");

    const cameraButton =
        document.getElementById("cameraButton");

    const modalCameraButton =
        document.getElementById("modalCameraButton");

    const profilePhotoInput =
        document.getElementById("profilePhotoInput");

    const profileForm =
        document.getElementById("profileForm");


    /*
    |--------------------------------------------------------------------------
    | Open Modal
    |--------------------------------------------------------------------------
    */

    function openModal() {
        editModal.classList.add("show");
        document.body.style.overflow = "hidden";
    }


    /*
    |--------------------------------------------------------------------------
    | Close Modal
    |--------------------------------------------------------------------------
    */

    function closeModal() {
        editModal.classList.remove("show");
        document.body.style.overflow = "";
    }


    /*
    |--------------------------------------------------------------------------
    | Edit Button
    |--------------------------------------------------------------------------
    */

    openModalButton.addEventListener("click", function () {
        openModal();
    });


    /*
    |--------------------------------------------------------------------------
    | Close Button
    |--------------------------------------------------------------------------
    */

    closeModalButton.addEventListener("click", function () {
        closeModal();
    });


    cancelButton.addEventListener("click", function () {
        closeModal();
    });


    /*
    |--------------------------------------------------------------------------
    | Click Outside Modal
    |--------------------------------------------------------------------------
    */

    editModal.addEventListener("click", function (event) {

        if (event.target === editModal) {
            closeModal();
        }

    });


    /*
    |--------------------------------------------------------------------------
    | ESC Key
    |--------------------------------------------------------------------------
    */

    document.addEventListener("keydown", function (event) {

        if (event.key === "Escape") {
            closeModal();
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Main Camera
    |--------------------------------------------------------------------------
    */

    cameraButton.addEventListener("click", function () {

        openModal();

        setTimeout(function () {
            profilePhotoInput.click();
        }, 150);

    });


    /*
    |--------------------------------------------------------------------------
    | Modal Camera
    |--------------------------------------------------------------------------
    */

    modalCameraButton.addEventListener("click", function () {

        profilePhotoInput.click();

    });


    /*
    |--------------------------------------------------------------------------
    | Profile Photo Preview
    |--------------------------------------------------------------------------
    */

    profilePhotoInput.addEventListener("change", function () {

        const file = this.files[0];

        if (!file) {
            return;
        }


        /*
        | File type
        */

        const allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];

        if (!allowedTypes.includes(file.type)) {

            alert("Only JPG, PNG and WEBP images are allowed.");

            this.value = "";

            return;
        }


        /*
        | File size
        */

        if (file.size > 2 * 1024 * 1024) {

            alert("Profile photo must be less than 2 MB.");

            this.value = "";

            return;
        }


        /*
        | Create Preview
        */

        const imageURL = URL.createObjectURL(file);


        /*
        |--------------------------------------------------------------------------
        | Main Profile Photo
        |--------------------------------------------------------------------------
        */

        const mainPhoto =
            document.getElementById("mainProfilePhoto");

        const mainInitials =
            document.getElementById("mainProfileInitials");


        if (mainPhoto) {

            mainPhoto.src = imageURL;

        } else if (mainInitials) {

            const newImage =
                document.createElement("img");

            newImage.src = imageURL;

            newImage.id = "mainProfilePhoto";

            newImage.className = "profile-photo";

            newImage.alt = "Profile Photo";

            mainInitials.replaceWith(newImage);
        }


        /*
        |--------------------------------------------------------------------------
        | Modal Photo
        |--------------------------------------------------------------------------
        */

        const modalPhoto =
            document.getElementById("modalProfilePhoto");

        const modalInitials =
            document.getElementById("modalProfileInitials");


        if (modalPhoto) {

            modalPhoto.src = imageURL;

        } else if (modalInitials) {

            const newModalImage =
                document.createElement("img");

            newModalImage.src = imageURL;

            newModalImage.id = "modalProfilePhoto";

            newModalImage.className = "modal-photo";

            newModalImage.alt = "Profile Photo";

            modalInitials.replaceWith(newModalImage);
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Form Validation
    |--------------------------------------------------------------------------
    */

    profileForm.addEventListener("submit", function (event) {

        let valid = true;


        const name =
            document.getElementById("name");

        const username =
            document.getElementById("username");

        const email =
            document.getElementById("email");


        const nameError =
            document.getElementById("nameError");

        const usernameError =
            document.getElementById("usernameError");

        const emailError =
            document.getElementById("emailError");


        nameError.textContent = "";
        usernameError.textContent = "";
        emailError.textContent = "";


        /*
        |--------------------------------------------------------------------------
        | Name
        |--------------------------------------------------------------------------
        */

        if (name.value.trim() === "") {

            nameError.textContent =
                "Name is required.";

            valid = false;

        } else if (name.value.trim().length < 3) {

            nameError.textContent =
                "Name must contain at least 3 characters.";

            valid = false;
        }


        /*
        |--------------------------------------------------------------------------
        | Username
        |--------------------------------------------------------------------------
        */

        if (username.value.trim() === "") {

            usernameError.textContent =
                "Username is required.";

            valid = false;

        } else if (username.value.trim().length < 3) {

            usernameError.textContent =
                "Username must contain at least 3 characters.";

            valid = false;
        }


        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        */

        if (email.value.trim() !== "") {

            const emailPattern =
                /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailPattern.test(email.value.trim())) {

                emailError.textContent =
                    "Please enter a valid email address.";

                valid = false;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Stop Submit
        |--------------------------------------------------------------------------
        */

        if (!valid) {
            event.preventDefault();
        }

    });

});