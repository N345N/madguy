<?php include 'change_password_process.php' ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
</head>
<body>
    <div class="container p-3 border border-5 rounded-3" style="width: 35%;">
        <h1 class="display-6 text-center p-2 bg-light">Change Password</h1>
        <form action="change_password.php?code=<?php echo $code; ?>" method="post">
            <div class="row mb-3 justify-content-md-center">
                <div class="col-auto">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo $email; ?>" readonly>
                </div>
            </div>
            <div class="row mb-3 justify-content-md-center">
                <div class="col-auto">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3 justify-content-md-center">
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary" name="change">Change Password</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>