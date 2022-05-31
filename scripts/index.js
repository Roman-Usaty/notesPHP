$(function () {
    $("#privacyCheck").click(function () { 
        let isCheck = $(this)[0].checked
        if (isCheck) {
            $("#signUpInModalSubmit").prop("disabled", false);
        } else {
            $("#signUpInModalSubmit").prop("disabled", true);
        }
    })

    $("#signUpInModalSubmit").click(function () {
        if (validateForm($("#signUp"))) {
            sendAjaxForm("signUp", "UserController.php");
        }
        return false;
    })

    $("#logInInModalSubmit").click(function () {
        if (validateForm($("#logIn"))) {
            sendAjaxForm("logIn", "UserController.php");
        }
        return false;
    })

    $("#btnLogOut").click(function () { 
        sendAjaxForm("logOut", "UserController.php");
        return false;
    });

    $("#notesModalSubmit").click(function () { 
        if (validateForm($("#notes"))) {
            sendAjaxForm("notes", "UserController.php");
        }
        return false;
    });

    $(".exit__close").click(function () { 
        let cardId = $(this).attr('id');
        cardId = cardId.replace("card-", "");
        $.ajax({
            type: "POST",
            url: "UserController.php",
            data: {
                type: "deletenote",
                id: cardId
            },
            dataType: "html",
            success: function (data, textStatus, jqXHR) {  
                let toast = new bootstrap.Toast(document.getElementById('liveToast'))
                $(".toast-body").text(function (index, text) { return "The note was created successfully." });
                toast.show()
                window.location.reload()
            }, 
            error: function (jqXHR,  textStatus, errorThrown) {  
                textStatus = textStatus.charAt(0).toUpperCase() + textStatus.slice(1);
                try {
                    textStatus = textStatus.charAt(0).toUpperCase() + textStatus.slice(1);
                    let response = $.parseJSON(jqXHR.responseText)
                    $(".toast-body").text(function (index, text) { 
                        return textStatus + ". Code " + jqXHR.status + ": " + errorThrown + ". " +  response.Error
                    })
     
                    let toast = new bootstrap.Toast(document.getElementById('liveToast'))
                    toast.show()

                } catch (error) {
                    $(".toast-body").text(function (index, text) { 
                        return textStatus + ". Code " + jqXHR.status + ": " + errorThrown + ". An error has occurred on the server we are already working on it"
                    })
        
                    let toast = new bootstrap.Toast(document.getElementById('liveToast'))
                    toast.show()
                }
            }
        })
    })
})

function sendAjaxForm(ajax_form, url, method) {
    $.ajax({
        type: "POST",
        url: url,
        data: $("#"+ajax_form).serialize(),
        dataType: "html",
        success: function (data, textStatus, jqXHR) {
            let toast = new bootstrap.Toast(document.getElementById('liveToast'))
            if (ajax_form === "logOut") {

                $(".toast-body").text(function (index, text) { return "Successfully deauthorized user." });
                toast.show()
                window.location.reload()

            } else if (ajax_form === "notes") {

                $(".toast-body").text(function (index, text) { return "The note was created successfully." });
                $("#"+ajax_form+"InModalClose").trigger("click")
                $("#"+ajax_form+"ModalReset").trigger("click")
                window.location.reload()
                toast.show();

            } else if (ajax_form === "logIn" || ajax_form === "signUp") {

                let response = $.parseJSON(data)
            
                $(".toast-body").text(function (index, text) { return "Successfully authorized user: " + response.Username })
                $("#"+ajax_form+"InModalClose").trigger("click")
                $("#"+ajax_form+"ModalReset").trigger("click")

                
                toast.show()
                window.location.reload()
            }
        },
        error: function (jqXHR,  textStatus, errorThrown) {  
            textStatus = textStatus.charAt(0).toUpperCase() + textStatus.slice(1);
            try {
                let response = $.parseJSON(jqXHR.responseText)
                $(".toast-body").text(function (index, text) { 
                    return textStatus + ". Code " + jqXHR.status + ": " + errorThrown + ". " +  response.Error
                })
    
                $("#"+ajax_form+"InModalClose").trigger("click")
                $("#"+ajax_form+"ModalReset").trigger("click")
    
                let toast = new bootstrap.Toast(document.getElementById('liveToast'))
                toast.show()
            } catch (error) {
                $(".toast-body").text(function (index, text) { 
                    return textStatus + ". Code " + jqXHR.status + ": " + errorThrown + ". An error has occurred on the server we are already working on it"
                })
    
                $("#signUpInModalClose").trigger("click")
                $("#signUpModalReset").trigger("click")
    
                let toast = new bootstrap.Toast(document.getElementById('liveToast'))
                toast.show()
            }
        }
    });
}


function validateForm(form) {
    let arrCheck = [];
    $(form).serializeArray().forEach(element => {
        switch (element.name) {
            case 'username':

                let usernamePattern = /^\w+$/;

                if (!usernamePattern.test(element.value) || element.value.length > 18) {

                    $("#" + $(form).attr('id') + "InputUsername").addClass("is-invalid");

                    if ($("." + $(form).attr('id') + "UsernameGroup>.invalid-feedback").length == 0) {

                        $("." + $(form).attr('id') + "UsernameGroup").append(`<div class="invalid-feedback">
                            Please enter a username consisting of letters of the English alphabet, numbers and an underscore or not exceeding the length of 18 characters
                            </div>`
                        );

                    }
                    
                    arrCheck.push(false);

                } else {

                    $("#" + $(form).attr('id') + "InputUsername").removeClass("is-invalid");
                    $("." + $(form).attr('id') + "UsernameGroup>.invalid-feedback").remove();

                }
            break;
            case 'email':

                let emailPattern = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/;

                if (!emailPattern.test(element.value)) {

                    $("#" + $(form).attr('id') + "InputEmail").addClass("is-invalid");
                    if ($("." + $(form).attr('id') + "EmailGroup>.invalid-feedback").length == 0) {
                        $("." + $(form).attr('id') + "EmailGroup").append(`<div class="invalid-feedback">
                            Please enter a valid email address
                            </div>`
                        );

                    }

                    arrCheck.push(false);

                } else {

                    $("#" + $(form).attr('id') + "InputEmail").removeClass("is-invalid");
                    $("." + $(form).attr('id') + "EmailGroup>.invalid-feedback").remove();

                }
            break;
            case 'password':

                if (element.value.length > 15 && element.value.length < 6) {

                    $("#" + $(form).attr('id') + "InputPassword").addClass("is-invalid");

                    if ($("." + $(form).attr('id') + "PasswordGroup>.invalid-feedback").length == 0) {

                        $("." + $(form).attr('id') + "PasswordGroup").append(`<div class="invalid-feedback">
                            The length of your password should not exceed 15 characters or be shorter than 6 characters
                            </div>`
                        );

                    }

                    arrCheck.push(false);

                } else {

                    $("#" + $(form).attr('id') + "InputPassword").removeClass("is-invalid");
                    $("." + $(form).attr('id') + "PasswordGroup>.invalid-feedback").remove();

                }
            break;
            case 'repeatPassword':

                if (!(element.value === $("#" + $(form).attr('id') + "InputPassword").val())) {

                    $("#" + $(form).attr('id') + "RepeatPassword").addClass("is-invalid");

                    if ($("." + $(form).attr('id') + "RepeatGroup>.invalid-feedback").length == 0) {

                        $("." + $(form).attr('id') + "RepeatGroup").append(`<div class="invalid-feedback">
                            The password must match
                            </div>`
                        );

                    }

                    arrCheck.push(false);

                } else {

                    $("#" + $(form).attr('id') + "RepeatPassword").removeClass("is-invalid");
                    $("." + $(form).attr('id') + "RepeatGroup>.invalid-feedback").remove();

                }
            break;
            case 'checkPrivacyPolicy':

                if (!(element.value === 'on')) {

                    $("#" + $(form).attr('id') + "PrivacyCheck").addClass("is-invalid");

                    if ($("." + $(form).attr('id') + "CheckPrivacyPolicy>.invalid-feedback").length == 0) {

                        $("." + $(form).attr('id') + "CheckPrivacyPolicy").append(`<div class="invalid-feedback">
                            The checkbox should be checked
                            </div>`
                        );

                    }   

                    arrCheck.push(false);

                } else {

                    $("#" + $(form).attr('id') + "PrivacyCheck").removeClass("is-invalid");
                    $("." + $(form).attr('id') + "CheckPrivacyPolicy>.invalid-feedback").remove();

                }
            break;
            case 'text':
                let text = element.value.trim();
                if (text.length < 10) {
                    $("#" + $(form).attr('id') + "InputText").addClass("is-invalid");

                    if ($("." + $(form).attr('id') + "TextGroup>.invalid-feedback").length == 0) {

                        $("." + $(form).attr('id') + "TextGroup").append(`<div class="invalid-feedback">
                            Must be more than 10 characters (spaces are not taken into account)
                            </div>`
                        );

                    }
                    arrCheck.push(false);
                } else {

                    $("#" + $(form).attr('id') + "InputText").removeClass("is-invalid");
                    $("." + $(form).attr('id') + "TextGroup>.invalid-feedback").remove();

                }
            break;
        }
    });
    return !arrCheck.includes(false);
}



function setCookie(name, value, options = {}) {

    options = {
      path: '/',
      // при необходимости добавить другие значения по умолчанию
      ...options
    };
  
    if (options.expires instanceof Date) {
      options.expires = options.expires.toUTCString();
    }
  
    let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);
  
    for (let optionKey in options) {
      updatedCookie += "; " + optionKey;
      let optionValue = options[optionKey];
      if (optionValue !== true) {
        updatedCookie += "=" + optionValue;
      }
    }
  
    document.cookie = updatedCookie;
}

function deleteCookie(name) {
    setCookie(name, "", {
      'max-age': -1
    })
}