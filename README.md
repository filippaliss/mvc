# MVC Course - Black Jack Project

#### Scrutinizer
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/filippaliss/mvc/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/filippaliss/mvc/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/filippaliss/mvc/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/filippaliss/mvc/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/filippaliss/mvc/badges/build.png?b=main)](https://scrutinizer-ci.com/g/filippaliss/mvc/build-status/main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/filippaliss/mvc/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/code-intelligence)

## Prerequisites
Before getting started, make sure you have the following installed on your system:

- PHP (accessible via the terminal/command line)
- Composer, the PHP package manager

## Getting Started
Follow these steps to set up and run the project:

Make sure that you run PHP >=8.2 and Composer >=2.5.8 on your local machine.

Clone the repository by running the following command in your terminal:
``` bash
git clone https://github.com/filippaliss/mvc.git
```

Navigate to the root folder of the project:
``` bash
cd mvc
```

Start a local development server with the following command:
``` bash
php -S localhost:8888 -t public
```

Open your web browser and go to:
``` arduino
http://localhost:8888
```

## Notes
If you encounter issues related to dependencies, make sure to run:
``` bash
composer install
```

## Black Jack Project

This repository contains a fully functional Black Jack game developed as the course project. The project demonstrates object-oriented programming, game logic implementation, and web application development.

### Project Overview

The Black Jack game allows players to:
- 🎮 Play 1-3 hands simultaneously against an automated dealer
- 💰 Manage a virtual bank account with betting system
- 👤 Enter a player name for personalized experience
- ✂️ Split matching pairs
- 🎯 Follow official Black Jack rules

### Project Structure

The project is organized in clear sections:

**Core Game Classes** (`src/BlackJack/`):
- `BlackJackGame.php` - Main game logic and flow control
- `BlackJackPlayer.php` - Player management with bank account
- `BlackJackDealer.php` - Dealer/bank implementation
- `BlackJackHand.php` - Hand management and card value calculation

**Controller** (`src/Controller/`):
- `ProjectController.php` - Handles all project routes (/proj)

**Templates** (`templates/proj/`):
- `base.html.twig` - Base template with project-specific styling
- `index.html.twig` - Landing page (/proj)
- `about.html.twig` - About page (/proj/about)
- `game.html.twig` - Game setup and betting interface
- `play.html.twig` - Main game play interface

### Features Implemented

✅ **Krav 1: Landing Page**
- Landing page at `/proj` accessible from main navbar
- Distinct visual style with blue gradient background
- Custom navigation for project pages
- Professional Black Jack theme with green game table

✅ **Krav 2: About Page**
- Comprehensive about page at `/proj/about`
- Project description and technical implementation details
- Feature list and game rules
- Course requirements mapping

✅ **Krav 3: Black Jack Game**
- Player name registration system
- Bank account management (starting balance: 1000 credits)
- Betting system with customizable bet amounts
- Play 1-3 hands simultaneously
- Official Black Jack rules:
  - Dealer hits on 16 or below, stands on 17+
  - Black Jack pays 1.5x the bet
  - Push (tie) returns the bet
  - Aces count as 1 or 11
  - Face cards count as 10
- Split functionality for matching pairs
- Automatic win/lose/push calculation
- Session-based game state persistence

### Optional Features

✅ **Split Implementation**
- Players can split matching pairs into two separate hands
- Each split hand receives an additional card
- Requires additional bet equal to original bet

### Accessing the Project

Once the server is running:
1. Navigate to `http://localhost:8888/proj` for the landing page
2. Click "Play Game" to start
3. Enter your player name
4. Select bet amount and number of hands (1-3)
5. Play Black Jack following the on-screen instructions

### Documentation

The project includes:
- ✅ Complete inline PHPDoc documentation for all classes and methods
- ✅ Comprehensive README (this file)
- ✅ Code coverage reports in `docs/coverage/`
- ✅ PHPMetrics reports in `docs/metrics/`
- ✅ API documentation in `docs/api/`

### Code Quality

The project maintains high code quality standards:
- Scrutinizer badges showing quality score, coverage, and build status
- Well-documented code with PHPDoc comments
- Object-oriented design with clear separation of concerns
- Comprehensive test coverage (aim: >90%)
