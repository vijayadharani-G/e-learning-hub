# 🎓 E-Learning Website

A full-stack e-learning platform where students can register, enroll in free or premium courses, make payments, and earn certificates on completion.

Built as my main project using **HTML, CSS, JavaScript, PHP and MySQL**.

## ✨ Features

- **User registration and login**
- **15+ courses** across free and premium categories (Web Basics, JavaScript, PHP & MySQL and more)
- **Payment flow** for premium courses with receipt generation
- **Course completion certificates**
- **Course reviews** from students
- **Responsive design** that works on desktop, tablet and mobile

## 🛠️ Tech Stack

| Layer    | Technology              |
|----------|-------------------------|
| Frontend | HTML5, CSS3, JavaScript |
| Backend  | PHP                     |
| Database | MySQL                   |
| Server   | XAMPP (Apache + MySQL)  |
| Tools    | VS Code, Git, GitHub    |

## 📸 Screenshots

<img width="1357" height="635" alt="Screenshot 2026-11-25 153214" src="https://github.com/user-attachments/assets/6898f9b8-0bab-4cc8-9479-0e9199bcfd5f" />


## 🚀 How to Run Locally

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Download or clone this repository into the XAMPP `htdocs` folder:
   ```bash
   cd C:/xampp/htdocs
   git clone https://github.com/vijayadharani-G/e-learning-hub.git
   ```
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`) and create a new database (for example, `elearning_db`).
4. Import the project's `.sql` file into that database using the **Import** tab.
5. Open the PHP database connection file and make sure the credentials match your setup:
   ```php
   $host = "localhost";
   $user = "root";
   $password = "";
   $database = "elearning_db";
   ```
6. Open your browser and go to `http://localhost/e-learning-hub`.

## 🧠 What I Learned

- Designing a relational database for users, courses, payments and certificates
- Connecting a PHP backend with a MySQL database
- Building a complete payment, receipt and certificate flow
- Making a multi-page website responsive

## 🔮 Future Improvements

- Online payment gateway integration
- Video lessons and quizzes
- Email verification and password reset

## 👩‍💻 Author

**Vijayadharani G**
- [LinkedIn](https://www.linkedin.com/in/vijayadharanigovindaraj)
- [GitHub](https://github.com/vijayadharani-G)
- [Portfolio](https://vijayadharani-protfilo.netlify.app)
