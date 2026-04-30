# SZYBKI START - IDML TRANSLATOR

Aby natychmiast wrócić do pracy w przyszłej sesji, wykonaj te kroki:

1. **Uruchom serwer**: Kliknij dwukrotnie w `start.bat` i wejdź na [http://localhost:8001](http://localhost:8001).
2. **Instrukcja dla AI**: Jeśli pracujesz z nowym asystentem, powiedz mu:
   > "Przeanalizuj plik `helpers/xml_cleaner.php` i `DEVELOPMENT_PLAN.md`. Pamiętaj, że używamy Unicode do scalania jednostek (np. superscripts). Kontynuujemy rozwój zgodnie z planem."

## Główne założenia techniczne:
- **Scalanie**: IDML rozbija tekst (np. `H`, `2`, `O`). My go scalamy przed wysłaniem do GPT.
- **Unicode**: Używamy znaków takich jak `²`, `³`, `₀`, aby GPT rozumiało jednostki miary jako jeden symbol.
- **Bezpieczeństwo**: Kopia zapasowa plików sprzed zmian znajduje się w folderze `backup/`.

## Kolejne kroki:
Szczegółowy plan rozwoju znajdziesz w pliku `DEVELOPMENT_PLAN.md`.

---
*Status: Projekt po optymalizacji 06.04.2026. Wszystkie testy (test_comprehensive.php) zakończone sukcesem.*
