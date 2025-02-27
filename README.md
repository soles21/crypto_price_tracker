# Crypto Price Tracker

## Video Preview
[Watch 30-Second Frontend Demo (3 MB)](https://sdk.cyclonverse.com/crypto_tracker.mp4)

A real-time cryptocurrency price tracker built with Laravel, Laravel Livewire, Laravel Reverb WebSockets, and SQLite.


## Features

- Real-time price updates via WebSockets
- Aggregates prices from multiple exchanges (configurable)
- Parallelized price fetching for accuracy
- Responsive UI with visual indicators for price changes
- Live digital clock showing the current time
- REST API for initial data loading
- Error handling and automatic reconnection for network fluctuations
- Unit tests for core functionality

## Architecture Overview

The application follows a clean, modular architecture based on SOLID principles:

1. **Data Collection Layer**:
   - Price fetcher runs continuously at configurable intervals (default: 5 seconds)
   - Jobs are dispatched to fetch prices from multiple exchanges in parallel
   - Fetched prices are stored in cache temporarily to avoid repeated API calls

2. **Aggregation Layer**:
   - Processes collected exchange data through job batches
   - Calculates average prices and price changes
   - Persists data to SQLite database for historical tracking

3. **Broadcasting Layer**:
   - Laravel Reverb WebSocket server broadcasts real-time updates
   - Events dispatched when prices change, updating connected clients instantly
   - Automatic reconnection handling for network fluctuations

4. **Presentation Layer**:
   - Livewire components with reactive UI updates
   - Visual indicators highlight price changes with color-coded animations
   - REST API for programmatic access and initial data loading

5. **Background Processing**:
   - Supervisor manages queue workers and continuous price fetching
   - Database queue driver ensures reliability and persistence
   - Configurable concurrency levels to avoid database locking issues

## Technology Stack

- **Backend**: PHP 8.2, Laravel 12
- **Frontend**: Laravel Livewire, Tailwind CSS, Alpine.js
- **Database**: SQLite
- **Real-time**: Laravel Reverb WebSockets
- **Queue Processing**: Laravel Queue with database driver
- **Containerization**: Docker, Docker Compose
- **Testing**: PHPUnit

## Setup Instructions

### Prerequisites

- Docker and Docker Compose

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/soles21/crypto_price_tracker.git
   cd crypto_price_tracker
   ```

2. Create .env file:
   ```bash
   cp .env.example .env
   ```

3. Configure environment variables in .env:
   ```
   # Configure your crypto settings
   CRYPTO_API_KEY=your_api_key_here
   CRYPTO_PAIRS=BTCUSDC,BTCUSDT,ETHBTC
   CRYPTO_EXCHANGES=binance,mexc,huobi
   CRYPTO_FETCH_INTERVAL=5
   
   # Database configuration
   DB_CONNECTION=sqlite
   
   # Reverb WebSocket
   REVERB_APP_ID=crypto_price_tracker
   REVERB_APP_KEY=your_reverb_app_key_here
   REVERB_HOST="reverb"
   REVERB_PORT=8080
   
   # For frontend WebSocket connections
   VITE_REVERB_HOST="localhost"
   VITE_REVERB_PORT=8080
   VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
   ```

4. Build and start the application:
   ```bash
   docker-compose build
   docker-compose up -d
   ```

5. Initialize the database:
   ```bash
   docker-compose exec app php artisan migrate
   ```

6. Generate application key:
   ```bash
   docker-compose exec app php artisan key:generate
   ```

7. Access the application:
   ```
   http://localhost:13579
   ```

### Stopping the Application

```bash
docker-compose down
```

## Configuration Options

The application can be configured via environment variables:

- `CRYPTO_PAIRS`: Comma-separated list of cryptocurrency pairs to track
- `CRYPTO_EXCHANGES`: Comma-separated list of exchanges to fetch prices from
- `CRYPTO_FETCH_INTERVAL`: Interval in seconds between price fetches
- `CRYPTO_API_KEY`: Your API key for FreeCryptoAPI

## API Endpoints

- `GET /api/prices`: Get all current cryptocurrency prices
- `GET /api/prices/{pair}`: Get current price for a specific cryptocurrency pair

## WebSocket Events

- Channel: `crypto-prices`
- Event: `crypto.price.updated`

## Running Tests

```bash
docker-compose exec app php artisan test
```

## Design Decisions and Trade-offs

### Docker-based Architecture
The application is fully containerized with dedicated services for web, queue processing, and WebSockets. This provides isolation and scalability but increases complexity compared to a single-container approach.

### SQLite Database
Used SQLite for simplicity and portability. This works well for smaller deployments, but for production environments with heavy loads, consider switching to MySQL or PostgreSQL due to SQLite's concurrency limitations.

### Job Batching
Used Laravel's job batching feature to parallelize price fetching. This ensures that we're getting prices from all exchanges at roughly the same time for more accurate averages, at the cost of increased memory usage.

### Reduced Worker Processes
Limited the number of queue workers to prevent SQLite database locking issues. This reduces throughput but improves stability when using SQLite as the queue and application database.

### Supervisor Process Management
Implemented Supervisor to manage long-running processes including the price fetcher, queue workers, and scheduler. This provides reliability but requires additional configuration compared to simpler approaches.

### Service-based Communication
Used Docker's internal networking to allow services to communicate. Backend services use container names for communication (e.g., "reverb" host), while the frontend uses localhost and exposed ports.

### Caching Strategy
Implemented a short-lived cache (1 minute) for API responses to reduce load on the external API while maintaining reasonable data freshness.

### Error Handling and Logging
Comprehensive error handling with detailed logging throughout the application helps with troubleshooting but increases code complexity.

### Alpine.js for UI Animations
Used Alpine.js for interactive UI elements and animations. This provides a reactive experience without the overhead of a full JavaScript framework, though with some limitations in complex state management.

## Known Issues and Limitations

- **SQLite Concurrency**: Under high load, SQLite may experience "database locked" errors due to its limited concurrency support.
- **WebSocket Connection**: Initial WebSocket connection may fail and require a page refresh if the Reverb server isn't fully started when the client loads.
- **CSS Animation Performance**: On lower-end devices, the price change animations may cause performance issues.
- **External API Dependency**: The FreeCryptoAPI has rate limits that may affect fetching frequency.
- **Error Recovery**: While the system handles many errors, some edge cases might require manual intervention.
- **Container Memory Usage**: The Docker setup might require optimization for production use to reduce memory footprint.
- **Limited Historical Data**: Currently, the application shows only the last known price for each pair without historical charting.

## Future Improvements

- Add historical price charting with time-series visualization
- Implement data persistence strategies for high-volume environments
- Add PostgreSQL as an alternative database option for better concurrency
- Implement user authentication and personalized watchlists
- Add more detailed exchange-specific information
- Create a price alerts feature with email/SMS notifications
- Enhance test coverage with feature and integration tests
- Implement circuit breakers for API calls to prevent cascading failures
- Add monitoring and health check endpoints
- Optimize Docker images for production deployment
- Implement horizontal scaling capabilities for high-traffic scenarios

## Contributing

1. Fork the repository
2. Create your feature branch: `git checkout -b feature/my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin feature/my-new-feature`
5. Submit a pull request