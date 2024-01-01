# Changelog

All notable changes to this project will be documented in this file.

## [1.0.1] - 2024-01-01

### Added

- Improved PDF Security: PDF files are now stored in the `uploads/gls-plugin` directory, which is protected by a .htaccess file. This ensures that the files are not directly accessible via a URL and can only be downloaded by administrators.

### Fixed

- Fixed Return Label Issue: The "Shipper" on the return labels has been corrected to display the actual customer. This was achieved by swapping the corresponding values in the label creation function.

## [1.0.0] - 2023-12-31

Initial release.
