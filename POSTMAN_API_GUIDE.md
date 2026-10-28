# U-PARK JSON API documentation

## 1. Overview

The U-PARK API is a PHP JSON endpoint backed by the project's MySQL database. It exposes public read-only event, parking-slot, and occupancy-summary data. Reservations can be created, edited, and cancelled with a reservation passcode.

The endpoint file is `api.php`. With the project installed in XAMPP at `C:\xampp\htdocs\upark`, the base URL is:

```text
http://localhost/upark/api.php
```

Start Apache and MySQL in XAMPP before making requests. Postman does not need an API key or bearer token. Mutating reservation requests require the passcode supplied when the reservation was created.

All responses use `Content-Type: application/json; charset=utf-8` and disable caching. Request bodies for POST, PUT, and DELETE must be JSON and should include:

```text
Content-Type: application/json
Accept: application/json
```

## 2. Endpoint summary

| Method | URL | Description | Success status |
|---|---|---|---:|
| GET | `/api.php?resource=events` | List campus events | 200 |
| GET | `/api.php?resource=slots` | List all parking slots | 200 |
| GET | `/api.php?resource=slots&status=available` | Filter slots by status | 200 |
| GET | `/api.php?resource=summary` | Get total, available, occupied, and reserved counts | 200 |
| POST | `/api.php?resource=reservations` | Create a reservation | 201 |
| PUT | `/api.php?resource=reservations&id={id}` | Edit a reservation | 200 |
| DELETE | `/api.php?resource=reservations&id={id}` | Cancel a reservation and release its slot | 200 |

The resource can also be supplied through `PATH_INFO` when the server supports it, but the `resource` query parameter shown above is the recommended XAMPP/Postman form. There is no separate `purpose` endpoint. Use `event_id` on a reservation to associate it with a campus event, or `null` for general parking.

## 3. Read endpoints

### List events

```http
GET http://localhost/upark/api.php?resource=events
```

Example response:

```json
{
  "data": [
    {
      "id": 2,
      "event_name": "University Week",
      "event_date": "2026-11-20",
      "location": "UNO-R Campus",
      "description": "Campus celebration"
    }
  ]
}
```

`data` is an array. It can be empty if there are no events. Only the event ID, name, date, location, and description are returned.

### List parking slots

```http
GET http://localhost/upark/api.php?resource=slots
```

To filter the list, use one of these values for `status`:

```text
all | available | reserved | occupied
```

Example:

```http
GET http://localhost/upark/api.php?resource=slots&status=available
```

Example response:

```json
{
  "data": [
    {
      "id": 3,
      "slot_number": "A-03",
      "zone": "Front Parking",
      "status": "available"
    }
  ]
}
```

The response does not expose reservation passcodes or driver information. An unsupported status returns HTTP 422.

### Parking summary

```http
GET http://localhost/upark/api.php?resource=summary
```

Example response:

```json
{
  "data": {
    "total": 40,
    "available": 28,
    "occupied": 7,
    "reserved": 5
  }
}
```

Counts come from the current `parking_slots` table. Depending on the PDO/MySQL configuration, database count values may be serialized as JSON numbers or strings.

## 4. Reservation endpoints

### Create a reservation

```http
POST http://localhost/upark/api.php?resource=reservations
```

In Postman, choose **Body → raw → JSON** and send:

```json
{
  "driver_name": "Postman Demo Driver",
  "plate_number": "TEST 123",
  "event_id": null,
  "slot_id": 3,
  "custom_key": "DemoPass1234"
}
```

Replace `slot_id` with an existing available slot ID from the slots endpoint. Set `event_id` to an existing event ID to associate it with an event, or leave it `null`.

On success, the API atomically marks the slot reserved and creates the reservation:

```http
HTTP/1.1 201 Created
```

```json
{
  "message": "Reservation created.",
  "data": {
    "id": 12,
    "reservation_code": "UPARK-A1B2C3D4",
    "slot_id": 3,
    "status": "reserved"
  }
}
```

Save `data.id` for the PUT and DELETE requests. The passcode is never returned in the response. Keep the passcode you sent; it is needed to edit or cancel this reservation.

### Update a reservation

```http
PUT http://localhost/upark/api.php?resource=reservations&id=12
```

The reservation ID can alternatively be included as `id` in the JSON body. Send the passcode and the updated driver and plate values:

```json
{
  "cancellation_key": "DemoPass1234",
  "driver_name": "Updated Demo Driver",
  "plate_number": "TEST 456"
}
```

Example success response:

```json
{
  "message": "Reservation updated.",
  "data": {
    "id": 12,
    "driver_name": "Updated Demo Driver",
    "plate_number": "TEST 456"
  }
}
```

Only active reservations with the correct passcode can be updated. The passcode comparison ignores spaces and punctuation and is case-insensitive, matching the existing U-PARK reservation behavior.

### Cancel a reservation

```http
DELETE http://localhost/upark/api.php?resource=reservations&id=12
```

In Postman, choose **Body → raw → JSON**:

```json
{
  "cancellation_key": "DemoPass1234"
}
```

Example success response:

```json
{
  "message": "Reservation cancelled.",
  "data": {
    "id": 12,
    "status": "cancelled"
  }
}
```

Cancellation changes the reservation status to `cancelled` and marks its parking slot `available`. Use the reservation ID returned by POST and the same passcode. The API does not provide a GET reservation-by-ID endpoint.

## 5. Validation and error responses

Errors use this format:

```json
{
  "error": "A short explanation of the problem."
}
```

| HTTP status | Meaning | Common cases |
|---:|---|---|
| 200 | Request succeeded | GET, PUT, or DELETE success |
| 201 | Reservation created | POST reservation success |
| 400 | Invalid JSON | Malformed JSON or a JSON scalar instead of an object |
| 403 | Reservation could not be authorized | Reservation is inactive/not found or passcode is incorrect |
| 404 | Unknown resource | Resource is not `events`, `slots`, `summary`, or `reservations` |
| 405 | Method not allowed | Trying to write to a read-only resource or GET reservations |
| 409 | Slot conflict | Slot does not exist or is no longer available |
| 413 | Request too large | JSON body exceeds 16 KB |
| 422 | Invalid field value | Missing/too-long text, invalid ID, bad status filter, or missing event |
| 500 | Server/database query error | Generic API failure; details are kept in the server error log |
| 503 | Database unavailable | MySQL is stopped or the database connection is unavailable |

The API accepts GET, POST, PUT, and DELETE methods. Events, slots, and summary are read-only; reservation mutations use POST, PUT, and DELETE. No arbitrary URL or upstream proxy is involved.

## 6. Postman setup and demonstration

1. Start Apache and MySQL in XAMPP.
2. In Postman, select **Import** and import `postman/U-PARK Local API.postman_collection.json`.
3. If your project URL differs, edit the collection variable `base_url`. The default is `http://localhost/upark/api.php`.
4. Run the collection requests in order: GET Events, GET Available Slots, GET Parking Summary, POST Create Reservation, PUT Update Reservation, DELETE Cancel Reservation.
5. The available-slots request saves an available `slot_id`; POST saves the new `reservation_id`; PUT and DELETE use those saved values.
6. The final DELETE cleans up the reservation created by the collection and reopens its slot.

The create/update/cancel requests change the local database. Run the collection against a local development database and make sure a slot is available. Do not use a real person's personal information or passcode in a classroom demo.

The admin event-management page also has a **U-PARK API JSON Viewer** for the three GET resources. It displays live JSON, HTTP status, and response time. POST, PUT, and DELETE are currently demonstrated through Postman.

## 7. Security and behavior notes

- GET data is public, consistent with the public parking portal.
- Creating a reservation is also public, consistent with the current booking form. The client supplies a passcode for later edits/cancellation.
- Reservation passcodes are not returned in API responses and are not written to the API request history.
- Reservation IDs and passcodes are required for PUT/DELETE authorization. Incorrect passcodes receive the same response as missing or inactive reservations.
- Reservation creation uses a transaction and only reserves a slot that is still marked available.
- The API is local PHP/MySQL code; Postman can call it directly without browser CORS access. The browser-based admin JSON viewer uses same-origin GET requests.
- This API is intended for local coursework/demo use. It does not implement user accounts, bearer tokens, rate limits, or production deployment hardening.