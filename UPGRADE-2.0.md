# Upgrade from 1.x to 2.0

- Minimum PHP is **8.2** (`^8.2`).
- Requires `portphp/portphp` **^2.0**.
- Requires `doctrine/dbal` **^3.0 || ^4.0** (DBAL 2.x is no longer supported).
- `DbalReader` uses the DBAL 3/4 Result API (`executeQuery` / `fetchAssociative`).
- `DbalReaderFactory` no longer implements the file-based `ReaderFactory` interface (it is a SQL factory).
- CI is GitHub Actions (Travis/Scrutinizer removed).
