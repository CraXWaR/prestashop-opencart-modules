<?php
// Heading
$_['heading_title'] = 'Energy Label';

// Tabs
$_['tab_settings'] = 'Settings';
$_['tab_classes'] = 'Energy Classes';
$_['tab_energy_efficiency'] = 'Energy Efficiency';
$_['tab_bulk'] = 'Bulk Import / Export';

// General text
$_['text_extension'] = 'Extensions';
$_['text_home'] = 'Home';
$_['text_edit'] = 'Edit Energy Label Settings';
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';
$_['text_yes'] = 'Yes';
$_['text_no'] = 'No';
$_['text_none'] = '-- Not used --';

// Success messages
$_['text_success'] = 'Success: Energy Label module settings have been saved!';
$_['text_class_added'] = 'Success: Energy class has been added!';
$_['text_class_updated'] = 'Success: Energy class has been updated!';
$_['text_class_deleted'] = 'Success: Energy class has been deleted!';
$_['text_import_success'] = 'Import complete: %d product(s) updated, %d skipped.';
$_['text_importing'] = 'Importing, please wait…';
$_['text_no_file_selected'] = 'Please select an Excel or CSV file before importing.';

// Buttons
$_['button_save'] = 'Save';
$_['button_save_stay'] = 'Save & Stay';
$_['button_cancel'] = 'Cancel';
$_['button_add_class'] = 'Add Class';
$_['button_save_class'] = 'Save';
$_['button_edit'] = 'Edit';
$_['button_delete'] = 'Delete';
$_['button_browse'] = 'Browse';
$_['button_clear'] = 'Clear';
$_['button_remove_file'] = 'Remove';
$_['button_export'] = 'Download Excel';
$_['button_import'] = 'Upload & Import';

// Settings tab
$_['label_status'] = 'Status';
$_['label_show_product'] = 'Show on Product Page';
$_['label_show_category'] = 'Show in Categories';
$_['label_custom_css'] = 'Custom CSS';
$_['help_custom_css'] = 'Injected as a &lt;style&gt; block in &lt;head&gt; on every page where the module loads.';

// Energy Classes tab
$_['label_classes'] = 'Energy Classes';
$_['column_class_name'] = 'Class Name';
$_['column_class_icon'] = 'Icon';
$_['column_sort_order'] = 'Sort Order';
$_['column_action'] = 'Action';

// Product Energy Efficiency tab
$_['label_cooling'] = 'Cooling';
$_['label_heating'] = 'Heating';
$_['label_general'] = 'General';
$_['label_energy_class'] = 'Energy Class';
$_['label_eu_label'] = 'EU Energy Label';
$_['label_datasheet'] = 'Product Datasheet';
$_['help_eu_label'] = 'Accepted formats: jpg, jpeg, png, webp, pdf. Max 10 MB.';
$_['help_datasheet'] = 'Accepted format: pdf. Max 10 MB.';
$_['confirm_remove_file'] = 'Are you sure you want to remove this file?';
$_['label_show_search'] = 'Show badges on Search page';
$_['label_show_special'] = 'Show badges on Special Offers page';
$_['label_show_manufacturer'] = 'Show badges on Manufacturer page';
$_['label_show_journal3'] = 'Show badges in Journal3 Products widget';

// Bulk Import / Export tab
$_['label_export'] = 'Export Labels';
$_['label_import'] = 'Import Labels';
$_['label_import_excel'] = 'Excel / CSV file (.xlsx or .csv)';
$_['label_import_zip'] = 'Files ZIP (optional)';
$_['help_export'] = 'Downloads an Excel file with all products, their current energy classes and existing file names. Fill in the columns and re-upload to update in bulk.';
$_['help_import'] = 'Upload the filled Excel (.xlsx) or CSV. Only non-empty cells will be updated; blank cells are left unchanged.';
$_['help_import_zip'] = 'ZIP must contain folders: cooling/, heating/, general/ — place label and datasheet files inside the matching folder. Write the full filename including extension in the Excel cell (e.g. label.pdf, image.jpg).';

// Errors
$_['error_warning'] = 'Warning: Please check the form carefully for errors!';
$_['error_permission'] = 'Warning: You do not have permission to modify the Energy Label module!';
$_['error_icon'] = 'Invalid icon.';
$_['error_class_name_required'] = 'Warning: Class name is required!';
$_['error_class_name_duplicate'] = 'Warning: A class with this name already exists!';
$_['error_invalid_class'] = 'Warning: Invalid class ID!';
$_['error_class_in_use'] = 'Warning: This class cannot be deleted because it is used by %d product(s)!';
$_['error_import_no_file'] = 'No file was uploaded.';
$_['error_import_file_type'] = 'Invalid file type. Please upload an .xlsx or .csv file.';
$_['error_import_zip_type'] = 'Invalid ZIP file. Please upload a .zip file.';
$_['error_import_empty'] = 'The uploaded file contains no data rows.';
$_['error_import_parse'] = 'Could not parse the file:';