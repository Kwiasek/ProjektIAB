# Dodana funkcjonalność opinii obiektów sportowych

## Zmienione/dodane pliki

### Modele
- **src/models/Review.php** (nowy)
  - Metody do zarządzania opiniami (dodawanie, edycja, usuwanie, pobieranie)
  - Funkcjonalność obliczania średniej oceny i rozkładu

### Kontrolery
- **src/controllers/ReviewController.php** (nowy)
  - Obsługa endpointów API dla opinii
  - Walidacja uprawnień (autor/właściciel obiektu)

### Trasy
- **routes/web.php** (zaktualizowany)
  - Dodane trasy POST/GET dla opinii
  - `/api/reviews/add` - dodawanie opinii
  - `/api/reviews/update` - edycja opinii
  - `/api/reviews/delete` - usuwanie opinii
  - `/api/facility/reviews` - pobieranie opinii dla obiektu
  - `/api/facility/user-review` - sprawdzenie opinii użytkownika

### Widoki
- **views/reservations/list.php** (zaktualizowany)
  - Przycisk "Oceń" dla przeszłych rezerwacji
  - Modal do dodania opinii z gwiazdkami (1-5) i polem komentarza
  - Przycisk znika po dodaniu opinii (jeśli użytkownik już ocenil)

- **views/facilities/facility.php** (zaktualizowany)
  - Sekcja opinii użytkowników
  - Podsumowanie ocen (średnia ocena, rozkład ocen w%)
  - Wyświetlanie opinii z imieniem autora, datą i komentarzem
  - Kontrolki edycji/usuwania dla autora opinii
  - Kontrolka usuwania dla właściciela obiektu
  - Modal do edycji opinii
  - Pełny JavaScript do obsługi gwiazdek (półgwiazdki, interaktywność)

## Funkcjonalność

### Dla użytkownika
1. Na stronie "Moje rezerwacje" dla przeszłych rezerwacji pojawia się przycisk "Oceń"
2. Kliknięcie przycisku otwiera modal z formularzem opinii
3. Użytkownik wybiera ocenę (1-5 gwiazdek) poprzez kliknięcie
4. Można dodać komentarz (opcjonalny)
5. Po wysłaniu opinii przycisk znika (można dodać tylko jedną opinię)
6. Użytkownik może edytować swoją opinię kliknięciem "Edytuj"
7. Użytkownik może usunąć swoją opinię kliknięciem "Usuń"

### Dla właściciela obiektu
- Widzi przycisk "Usuń" przy każdej opinii
- Może usuwać niedostępne/niewłaściwe opinie

### Na stronie obiektu sportowego
- Sekcja opinii z:
  - Średnią oceną (np. 4.5/5)
  - Wizualizacją gwiazdek
  - Rozkładem ocen w percentach (5★ 40%, 4★ 30%, itd.)
  - Listą wszystkich opinii
  - Każda opinia zawiera: autora, ocenę, datę, komentarz
  - Opcje edycji/usuwania dla właściciela opinii

## Baza danych
Tabela `facility_reviews` już istniała w schemacie:
```sql
CREATE TABLE IF NOT EXISTS facility_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    facility_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (facility_id) REFERENCES facilities(id)
);
```

## Obsługa ocen
- Gwiazdki są interaktywne (hover pokazuje gwiazdki aż do hovered)
- Możliwe do wyboru są oceny od 1 do 5
- Edycja opinii umożliwia zmianę zarówno oceny jak i komentarza
