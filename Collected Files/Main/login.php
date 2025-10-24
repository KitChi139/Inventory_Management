<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Page</title>

  <!-- Font Awesome for icons -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
  />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      background-color: #f2f6fb;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      text-align: center;
      background: #ffffff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      width: 400px;
      padding: 40px 35px;
    }

    h2 {
      font-weight: 600;
      color: #001f3f;
      margin-bottom: 6px;
    }

    .subtitle {
      font-size: 14px;
      color: #555;
      margin-bottom: 30px;
    }

    .input-group {
      position: relative;
      margin-bottom: 25px;
      text-align: left;
    }

    .input-group label {
      display: block;
      font-size: 14px;
      color: #000;
      margin-bottom: 5px;
    }

    .input-group input {
      width: 100%;
      border: none;
      border-bottom: 1px solid #000;
      padding: 8px 35px 8px 5px;
      outline: none;
      background: transparent;
      font-size: 14px;
    }

    .input-group input:focus {
      border-bottom: 1px solid #003366;
    }

    /* FIXED ICON ALIGNMENT */
    .input-group .icon {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-35%);
      color: #001f3f;
      font-size: 16px;
    }

    .btn {
      width: 100%;
      background-color: #003366;
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: 10px 0;
      font-size: 15px;
      cursor: pointer;
      margin-top: 10px;
      transition: background 0.3s;
    }

    .btn:hover {
      background-color: #002b5b;
    }

    @media (max-width: 480px) {
      .container {
        width: 90%;
        padding: 30px 20px;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <h2>Sign in to your Account</h2>
    <p class="subtitle">Enter your details to access the portal</p>

    <form>
      <div class="input-group">
        <label>Username / Email Address</label>
        <input type="text" />
        <i class="fa-solid fa-user icon"></i>
      </div>

      <div class="input-group">
        <label>Password</label>
        <input type="password" />
        <i class="fa-solid fa-lock icon"></i>
      </div>

      <button type="submit" class="btn">Login</button>
    </form>
  </div>
</body>
</html>