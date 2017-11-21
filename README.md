# Ladder-Site
Website created for a Client-Server class, which maintains an interactive challenge ladder for a competitive game.

If the server is running, the site can be accessed at: http://csis314-jjones.bitnamiapp.com/

Note that the email entered in the registration page will never be used or displayed. Theoretically, it could be used to send alerts when
that person was challenged by another user.

Security measures taken:
  - One-way password encryption and verification used to authenticate users.
  - Users not logged in will be redirected to the home page if they attempt to access other pages via URL.
  - htmlspecialchars() used to prevent XSS attacks when displaying user-provided data.
  - HTTPS is used when sending sensitive form data to the server.
  - The user will be logged out and returned to the home page if inactive on a logged-in page for five minutes.
  - No-cache pragma used to request that browser not cache logged-in pages. (least significant security measure)
