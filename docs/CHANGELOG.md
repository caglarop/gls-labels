# Changelog

All notable changes to this project will be documented in this file.

## [1.0.7] - 24-04-24

- WP File System 

## [1.0.5] - 24-01-05

- Added parcel cancellation feature.
- Added new translations to .po language files.
- Updated order note to include a link for parcel cancellation.

## [1.0.4] - 24-01-01

### Changed

- Refactored plugin code for improved efficiency and readability.

## [1.0.3] - 2024-01-01

### Changed

- Renamed the plugin for better clarity and searchability.

## [1.0.2] - 2024-01-01

### Added

- Added configuration button to metabox, which is displayed when the plugin is not configured.

### Changed

- Improved PHP compatibility. The plugin is now compatible with PHP versions from 7.4 to 8.3.
- For orders without a specified weight, a default weight of 0.1kg is now assigned. This change is due to GLS requiring a parcel weight.

## [1.0.1] - 2024-01-01

### Added

- Improved PDF Security: PDF files are now stored in the `uploads/gls-plugin` directory, which is protected by a .htaccess file. This ensures that the files are not directly accessible via a URL and can only be downloaded by administrators.

### Fixed

- Fixed Return Label Issue: The "Shipper" on the return labels has been corrected to display the actual customer. This was achieved by swapping the corresponding values in the label creation function.

## [1.0.0] - 2023-12-31

Initial release.
