<?php
require_once __DIR__.'/boot.php';

$user = null;
if (check_token()) {
    $stmt = pdo()->prepare("SELECT * FROM users WHERE token = :token");
    $stmt->execute(['token' => $_SESSION['token']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC); 
}
?>



<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/index.css">
    <script src="https://kit.fontawesome.com/214148955f.js" crossorigin="anonymous"></script>
</head>
<body>
    <header>
        <div class="container d-flex justify-content-between pt-3">
            <div class="logo">
                <p>Notes</p>
            </div>
            
                <?php if ($user) { ?> 
                <div class="group-button profile  d-flex justify-content-between align-items-center">
                    <p style="margin-bottom: 0 !important;">Welcome back, <?=htmlspecialchars($user['username'])?></p>
                    <form id="logOut" action method="post">
                        <input name="type" value="logout" type="text" hidden>
                        <button id="btnLogOut" type="submit" class="btn btn-outline-primary">Log Out</button>
                    </form>
                </div>
                <?php } else {?>
                <div class="group-button d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-primary mr-4"
                        data-bs-toggle="modal" data-bs-target="#logInModal">LogIn</button>
                    <button type="button" class="btn btn-outline-primary"  
                        data-bs-toggle="modal" data-bs-target="#signUpInModal">SignUp</button>
                </div>
                <?php } ?>
            
        </div>
    </header>
    <div class="body"> 
    <?php if ($user) { ?> 
        <div class="container">
            <button type="button" class="btn btn-primary ms-4"
                data-bs-toggle="modal" data-bs-target="#notesModal">Create Note</button>  
        </div>
    <?php } ?>
        <div class="container list-notes cards">  
        <?php
        if ($user) {

            $stmt = pdo()->prepare("SELECT * FROM usersnotes where userid = :user");
            $stmt->execute(['user' => $user['id']]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($notes as $key => $value) {
                echo(
                    "<div class='card card-{$value['id']}'>
                        <p class='card__exit'>
                            <a class='exit__close' id='card-{$value['id']}'><i class='fas fa-times'></i></a>
                        </p>
                        <div class='card__icon d-flex'>
                            <i class='fas fa-bolt'></i>
                            <h2 class='ms-3 card__title'>{$value['name']}</h2>
                        </div>
                        <p class='card__body'>{$value['text']}</p>
                    </div>"
                );
            }

        } ?>
        <?php if (!$user) { ?>
            

            <div class="card card-1">
                <p class="card__exit">
                    <i class="fas fa-times"></i>
                </p>
                <div class="card__icon d-flex">
                    <i class="fas fa-bolt"></i>
                    <h2 class="ms-3 card__title">Welcome to Notes</h2>
                </div>
                <p class="card__body">If you want to add a Note, then SignUp\logIn now</p>
            </div>

            <div class="card card-2">
                <p class="card__exit">
                    <i class="fas fa-times"></i>
                </p>
                <div class="card__icon d-flex">
                    <i class="fas fa-bolt"></i>
                    <h2 class="ms-3 card__title">To the doctor</h2>
                </div>
                <p class="card__body">Go to the doctor tomorrow</p>
            </div>

            <div class="card card-3">
                <p class="card__exit">
                    <i class="fas fa-times"></i>
                </p>
                <div class="card__icon d-flex">
                    <i class="fas fa-bolt"></i>
                    <h2 class="ms-3 card__title">Lorem ipsum dolor sit</h2>
                </div>
                <p class="card__body">Lorem ipsum dolor sit amet consectetur adipisicing elit. Eos obcaecati totam corrupti molestias quia quos illum, error sequi laboriosam! Repellendus ad sapiente nihil tempore aut, quos tenetur deleniti provident ratione?</p>
            </div>
        <?php } ?>
        </div>
        
    </div>

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fa-solid fa-bell"></i>
                <strong id="notification__title" class="me-auto ms-1">Уведомление</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Закрыть"></button>
            </div>
            <div class="toast-body">

            </div>
        </div>
    </div> 
    <div class="modal fade" id="signUpInModal" tabindex="-1" aria-labelledby="signUpInModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="signUpInModalLabel">Sign Up</h5>
                    <button id="signUpInModalClose" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="signUp" method="POST" action onsubmit="return validateForm(this);">
                        <input name="type" value="signup" type="text" hidden>
                        <input id="signUpModalReset" type="reset" hidden>
                        <div class="mb-3 signUpUsernameGroup">
                            <label for="signUpInputUsername" class="form-label">Username</label>
                            <input name="username" type="text" class="form-control" id="signUpInputUsername" required>
                        </div>
                        <div class="mb-3 signUpEmailGroup">
                            <label for="signUpInputEmail" class="form-label">Email address</label>
                            <input name="email" type="email" class="form-control" id="signUpInputEmail" aria-describedby="emailHelp" required>
                            <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                        </div>
                        <div class="mb-3 signUpPasswordGroup">
                            <label for="signUpInputPassword" class="form-label">Password</label>
                            <input name="password" type="password" class="form-control" id="signUpInputPassword" required>
                        </div>
                        <div class="mb-3 signUpRepeatGroup">
                            <label for="signUpRepeatPassword" class="form-label">Repeat Password</label>
                            <input name="repeatPassword" type="password" class="form-control" id="signUpRepeatPassword" required>
                        </div>
                        <div class="mb-3 form-check signUpCheckPrivacyPolicy">
                            <input name="checkPrivacyPolicy" id="privacyCheck" type="checkbox" class="form-check-input" id="signUpPrivacyCheck" required>
                            <label class="form-check-label" for="signUpPrivacyCheck">I agree with the <a href="#">Privacy Policy</a></label>
                        </div>
                    </form>
                    <div class="signUpFormError" style="display: none;">
                        <p>Check the correctness of the entered data/p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="signUpInModalSubmit" type="submit" class="btn btn-primary" form="signUp" disabled>Sign Up</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="logInModal" tabindex="-1" aria-labelledby="logInModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logInModalLabel">Log In</h5>
                    <button id="logInInModalClose" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="logIn" method="POST" action onsubmit="return validateForm(this);">
                        <input name="type" value="login" type="text" hidden>
                        <input id="logInModalReset" type="reset" hidden>
                        <div class="mb-3 logInUsernameGroup">
                            <label for="logInInputUsername" class="form-label">Username</label>
                            <input name="username" type="text" class="form-control" id="logInInputUsername" required>
                        </div>
                        <div class="mb-3 logInPasswordGroup">
                            <label for="logInInputPassword" class="form-label">Password</label>
                            <input name="password" type="password" class="form-control" id="logInInputPassword" required>
                        </div>
                    </form>
                    <div class="logInFormError" style="display: none;">
                        <p>Check the correctness of the entered data</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="logInInModalSubmit" type="submit" class="btn btn-primary" form="logIn">Log In</button>
                </div>
            </div>
        </div>
    </div>
    <?php if ($user) { ?> 
    <div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notesModalLabel">Note</h5>
                    <button id="notesModalClose" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="notes" method="POST" action onsubmit="return validateForm(this);">
                        <input name="type" value="createnote" type="text" hidden>
                        <input id="notesModalReset" type="reset" hidden>
                        <div class="mb-3 notesNameGroup">
                            <label for="notesInputName" class="form-label">Name of the note</label>
                            <input name="name" type="text" class="form-control" id="notesInputName" required>
                        </div>
                        <div class="mb-3 notesTextGroup form-floating">
                            <textarea class="form-control" placeholder="Write a note" name="text" id="notesInputText" required></textarea>
                            <label for="notesInputText">Write a note</label>
                        </div>
                    </form>
                    <div class="notesFormError" style="display: none;">
                        <p>Check the correctness of the entered data</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="notesModalSubmit" type="submit" class="btn btn-primary" form="notes">Create Note</button>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
    <script src="library/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="scripts/index.js"></script>
</body>
</html>