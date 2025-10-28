# XAMPP PHP Configuration for Large File Uploads

## Problem
`PostTooLargeException` error when creating courses with large file uploads.

## Solutions

### 1. Modify XAMPP php.ini (Recommended for Development)

**Location:** `C:\xampp\php\php.ini`

Find and modify these values:

```ini
upload_max_filesize = 200M
post_max_size = 210M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
max_file_uploads = 50
```

**Important Notes:**
- `post_max_size` should be LARGER than `upload_max_filesize`
- Restart Apache after making changes

**How to Restart Apache:**
1. Open XAMPP Control Panel
2. Stop Apache
3. Start Apache again

### 2. .htaccess Configuration (Already Added)

The `.htaccess` file has been created with the necessary configurations.
This works if your Apache allows `.htaccess` overrides.

### 3. AJAX Form Submission (Already Implemented)

The form now submits via AJAX with:
- Progress bar showing upload progress
- Better error handling
- JSON responses from controller
- Timeout set to 5 minutes

### 4. Custom Middleware (Already Added)

A custom middleware `CheckPostSize` provides better error messages for file size issues.

## Testing After Configuration

1. Restart Apache in XAMPP Control Panel
2. Clear browser cache
3. Try creating a course with multiple files

## Current Upload Limits

- **Maximum file size:** 200MB
- **Maximum POST size:** 210MB
- **Execution timeout:** 5 minutes
- **Maximum files per upload:** 50

## Troubleshooting

### If still getting errors:

1. **Check actual php.ini location:**
   ```php
   <?php phpinfo(); ?>
   ```
   Look for "Loaded Configuration File"

2. **Verify Apache loaded new settings:**
   - Check phpinfo() for the values
   - Ensure you restarted Apache (not just PHP)

3. **Check Apache error logs:**
   `C:\xampp\apache\logs\error.log`

4. **Browser console errors:**
   - Open Developer Tools (F12)
   - Check Console and Network tabs for AJAX errors

### For Production Environment:

Consult your hosting provider to increase:
- `upload_max_filesize`
- `post_max_size`
- `max_execution_time`
- `memory_limit`

## Additional Notes

- The form now uses jQuery AJAX for better user experience
- Progress bar shows upload progress in real-time
- Large files are handled more gracefully
- Error messages are more informative
