import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Scanner;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

public class RepresentativeApp {

    public void register(Connection connection) {
        Scanner scanner = new Scanner(System.in);

        System.out.print("Enter username: ");
        String email = scanner.nextLine();

        System.out.print("Enter password: ");
        String password = scanner.nextLine();
        String hashedPassword = hashPassword(password);

        // Insert the representative into the representatives table
        String sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, email);
            statement.setString(2, hashedPassword);

            int rowsAffected = statement.executeUpdate();
            if (rowsAffected > 0) {
                System.out.println("Representative registered successfully!");
            } else {
                System.out.println("Failed to register representative.");
            }
        } catch (SQLException e) {
            System.err.println("Error registering representative: " + e.getMessage());
        }
    }

    public void loginAndManageParticipants(Connection connection) {
        Scanner scanner = new Scanner(System.in);

        System.out.print("Enter username: ");
        String email = scanner.nextLine();

        System.out.print("Enter password: ");
        String password = scanner.nextLine();
        String hashedPassword = hashPassword(password);

        // Validate login credentials
        if (validateLogin(connection, email, hashedPassword)) {
            System.out.println("Login successful!");

            while (true) {
                System.out.println("1. View registered participants");
                System.out.println("2. Update participant status");
                System.out.println("3. Logout");

                System.out.print("Choose an option: ");
                int option = scanner.nextInt();
                scanner.nextLine(); // consume newline

                switch (option) {
                    case 1:
                        viewRegisteredParticipants(connection);
                        break;
                    case 2:
                        updateParticipantStatus(connection, scanner);
                        break;
                    case 3:
                        System.out.println("Logging out...");
                        return;
                    default:
                        System.out.println("Invalid option. Please try again.");
                }
            }
        } else {
            System.out.println("Invalid login credentials.");
        }
    }

    private boolean validateLogin(Connection connection, String email, String hashedPassword) {
        String sql = "SELECT COUNT(*) FROM users WHERE email = ? AND password = ?";
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, email);
            statement.setString(2, hashedPassword);
            ResultSet resultSet = statement.executeQuery();
            resultSet.next();
            return resultSet.getInt(1) == 1;
        } catch (SQLException e) {
            System.err.println("Error validating login: " + e.getMessage());
            return false;
        }
    }

    private void viewRegisteredParticipants(Connection connection) {
        String sql = "SELECT * FROM participants";
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            ResultSet resultSet = statement.executeQuery();
            while (resultSet.next()) {
                System.out.println("ID: " + resultSet.getInt("id") + ", Username: " + resultSet.getString("username") +
                        ", Name: " + resultSet.getString("firstname") + " " + resultSet.getString("lastname") +
                        ", Email: " + resultSet.getString("email") + ", DOB: " + resultSet.getDate("date_of_birth") +
                        ", School Reg No: " + resultSet.getString("registration_number") +
                        ", Status: " + resultSet.getString("status"));
            }
        } catch (SQLException e) {
            System.err.println("Error viewing participants: " + e.getMessage());
        }
    }

    private void updateParticipantStatus(Connection connection, Scanner scanner) {
        System.out.print("Enter participant ID: ");
        int participantId = scanner.nextInt();
        scanner.nextLine(); // consume newline

        System.out.print("Enter new status (pending/confirmed/rejected): ");
        String newStatus = scanner.nextLine();

        if (!newStatus.equals("pending") && !newStatus.equals("confirmed") && !newStatus.equals("rejected")) {
            System.err.println("Invalid status input. Please enter pending, confirmed, or rejected.");
            return;
        }

        String sql = "UPDATE participants SET status = ? WHERE id = ?";
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, newStatus);
            statement.setInt(2, participantId);
            int rowsAffected = statement.executeUpdate();
            if (rowsAffected > 0) {
                System.out.println("Participant status updated successfully!");
            } else {
                System.out.println("Failed to update participant status.");
            }
        } catch (SQLException e) {
            System.err.println("Error updating participant status: " + e.getMessage());
        }
    }

    private String hashPassword(String password) {
        try {
            MessageDigest md = MessageDigest.getInstance("SHA-256");
            byte[] hash = md.digest(password.getBytes());
            StringBuilder hexString = new StringBuilder();
            for (byte b : hash) {
                hexString.append(String.format("%02x", b));
            }
            return hexString.toString();
        } catch (NoSuchAlgorithmException e) {
            throw new RuntimeException("Error hashing password", e);
        }
    }
}