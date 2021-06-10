# Minimal Filesystem Change Log

## 1.0.0

- Initial release.

  Supported actions:
    - Directories:
      - createDirectory
      - createDirectoryForFile
      - getCurrentDirectory
      - existsDirectory
      - isDirectory
    - Files:
      - writeFile
      - appendToFile
      - isReadableFile
      - readFile
      - renameFile
      - deleteFile
      - existsFile
      - isFile
    - Paths:
      - exists
    - Listing, searching:
      - listFiles
      - searchFiles
      - searchFilesRecursively
