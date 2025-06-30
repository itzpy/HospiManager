# HospiManger

A web-based inventory management system for hospitals to track medical supplies, lab instruments, and other inventory items.

## Overview

This system helps Ghanaian hospitals maintain accurate inventory tracking to prevent stockouts during medical emergencies. It enables proper management of inventory inflow and outflow.

## Features

- Inventory tracking and management
- Stock count monitoring
- Category-based item organization
- User role management (Superadmin, Admin, Staff)
- Activity logging
- Dashboard with analytics
- Low stock alerts

## User Roles

- **Superadmin**: Full system access, manages inventory, categories, and users
- **Admin**: Stock adjustment permissions
- **Staff**: View-only access to inventory

## Architecture

### Database Structure
- Users: Stores user information and roles
- Activity Log: Tracks user actions (linked to users and items)
- Items: Inventory items with category associations
- Categories: Classification system for inventory items

## Installation

1. Clone the repository:
```bash
git clone https://github.com/itzpy/Hospital_Management.git
```

2. Import the database:
- Locate `hospital_management.sql` in the `db/` folder
- Import into your MySQL server

## Access

- Website: http://169.239.251.102:3341/~papa.badu/Hospital_Management/index.php
- Demo Video: https://youtu.be/hT6E3GDYEVo

## Functions

- `Inventory_functions()`: Inventory management operations
- `Dashboard_functions()`: Navigation and analytics display
- `Category_functions()`: Category management
- `Item_functions()`: Item data management and stock operations

## Developer

Papa Yaw Badu  
Web Tech Summer 2024
