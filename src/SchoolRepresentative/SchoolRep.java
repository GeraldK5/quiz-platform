package SchoolRepresentative;

import java.io.IOException;
import java.sql.SQLException;
import java.util.Scanner;

public class SchoolRep {

    public static void main(String[] args) throws SQLException, InterruptedException, IOException {
        Scanner scanner = new Scanner(System.in);

        while (true) {
            System.out.println("LOGIN MENU");

            System.out.print("Enter registration number: ");
            String reg_no = scanner.nextLine();

            System.out.print("Enter password: ");
            String password = scanner.nextLine();

            String isValidUser = Authentication.validateUser(reg_no, password);

            if (!isValidUser.isEmpty() && !isValidUser.equals("connectionfailed")) {
                System.out.println("********Login successful*******");
                Functions.DisplayOptions(isValidUser, scanner);

            } else if (isValidUser.isEmpty()) {
                System.out.println("**********Invalid username or password*********");
            } else if (isValidUser.equals("connectionfailed")) {
                System.out.println("**********Exception caught, Failed to make a connection*********");
            }
        }
    }
}
