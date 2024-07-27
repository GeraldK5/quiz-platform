# Mathematics Challenge

The International Education Services is organizing a mathematics competition for primary school children across the country. This project is a web system designed to manage the competition, including participant registration, question management, challenge administration, and result reporting.

## Features

- **Administrator Functions:**
  - Upload schools with details such as name, district, school registration number, email, and representative name.
  - Validate and register school representatives.
  - Upload questions and answers from Excel documents (`questions.xlsx` and `answers.xlsx`).
  - Set challenge parameters (start date, end date, duration, number of questions).
  - Open and close challenges based on set parameters.
  - Generate reports and recognize winners.

- **Participant Functions:**
  - Register using a command line interface with details including username, first name, last name, email address, date of birth, school registration number, and image file.
  - View valid challenges and choose which to participate in.
  - Attempt challenges with random questions selected from the uploaded pool.
  - Receive scores and detailed reports of each attempt via email.

- **School Representative Functions:**
  - Confirm or reject new participant registrations.
  - Manage participant statuses via a command line interface.

- **Challenge and Reporting:**
  - Track time and scores during challenges.
  - Deduct marks for incorrect answers.
  - Generate participant reports with scores and time taken.
  - Provide various analytics including most correctly answered questions, school rankings, performance over time, percentage repetition of questions, and lists of best and worst performing schools.

## Analytics and Reports

The system provides detailed analytics and reports such as:

1. Most correctly answered questions.
2. School rankings.
3. Performance of schools and participants over the years and time.
4. Percentage repetition of questions for a given participant across attempts.
5. List of worst performing schools for a given challenge.
6. List of best performing schools for all challenges.
7. List of participants with incomplete challenges.
8. Additional reports to add value to the project.

## Installation

To set up the project locally, follow these steps:

1. **Clone the Repository:**
   ```sh
   git clone <repository-url>
   cd <repository-directory>
