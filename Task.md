# CSV Data Import and REST API

This project involves creating a database, importing data from a CSV file, and exposing it via a REST API.

## Requirements

- **MySQL** database to store the imported data.
- **PHP** with either plain PHP, **Laravel/Lumen**, or any other preferred framework.
- **Git** for version control, with multiple commits recommended during the development process.

### Steps to Complete

#### 1. **Create MySQL Database**
   - Set up a MySQL database to store the data.
   - Ensure that the database is normalized properly according to the data in the CSV.

#### 2. **CSV File Parsing and Database Population**
   - Open and transform the contents of the attached `.csv` file.
   - Populate the database with the data from the CSV file using the appropriate functionality in your project.

#### 3. **Data Retrieval and Display in JSON Format**
   - Implement functionality to read data from the database and display it in a structured JSON format.

#### 4. **REST API Routes**
   The following functionalities need to be implemented for the REST API:

   - Display all categories
   - Modify category names
   - Delete categories
   - Display all products
   - Display all products belonging to a specific category
   - Modify products
   - Delete products
   
   **Note:**  
   Creating products and categories is not required for this test, as they are already inserted during the CSV parsing process.

#### 5. **Use Git for Version Control**
   - Ensure that your project is version-controlled using Git.
   - It is recommended to commit your changes frequently during the development process.

#### Additional Information:
   - There is **no need to create a user interface** or an upload button for the CSV file.
   - The CSV processing functionality should be triggered through the **command line** or **CLI** commands associated with your chosen framework (e.g., Artisan for Laravel, Yii, etc.).

---

### Bonus Task

Create a new route to generate a CSV file for products belonging to a specific category.

- **Filename**: The filename should follow this template:  
  `category_name_year_month_day-hour_minute.csv`  
  Example: `printer_ink_2022_01_01-00_00.csv`
  
- **Filename Formatting**:  
  Any non-alphanumeric characters in the category name should be converted to underscores (`_`).
