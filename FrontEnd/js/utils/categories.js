// Cache for categories to avoid multiple API calls
let categoriesCache = null;
let lastFetchTime = 0;
const CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

/**
 * Fetches all categories from the server
 * @returns {Promise<Array>} Array of categories with id and name
 */
async function getAllCategories() {
    // Check if we have cached data that's still valid
    if (categoriesCache && (Date.now() - lastFetchTime) < CACHE_DURATION) {
        return categoriesCache;
    }

    try {
        const response = await fetch('http://localhost/Ecommerce/BackEnd/api/getCategories.php', {
            method: 'GET',
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.success && Array.isArray(data.categories)) {
            // Update cache
            categoriesCache = data.categories;
            lastFetchTime = Date.now();
            return data.categories;
        } else {
            throw new Error('Invalid response format');
        }
    } catch (error) {
        console.error('Error fetching categories:', error);
        throw error;
    }
}

/**
 * Gets a category name by its ID
 * @param {number} categoryId - The ID of the category
 * @returns {Promise<string>} The name of the category
 */
async function getCategoryNameById(categoryId) {
    try {
        const categories = await getAllCategories();
        const category = categories.find(cat => cat.id === categoryId);
        return category ? category.name : 'Unknown Category';
    } catch (error) {
        console.error('Error getting category name:', error);
        return 'Unknown Category';
    }
}

/**
 * Populates a select element with categories
 * @param {HTMLSelectElement} selectElement - The select element to populate
 * @param {number} [selectedId] - Optional ID of the category to select
 */
async function populateCategorySelect(selectElement, selectedId = null) {
    try {
        const categories = await getAllCategories();
        
        // Clear existing options
        selectElement.innerHTML = '<option value="">Select a category</option>';
        
        // Add categories as options
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            if (selectedId && category.id === selectedId) {
                option.selected = true;
            }
            selectElement.appendChild(option);
        });
    } catch (error) {
        console.error('Error populating category select:', error);
        selectElement.innerHTML = '<option value="">Error loading categories</option>';
    }
}

// Export functions for use in other files
window.getAllCategories = getAllCategories;
window.getCategoryNameById = getCategoryNameById;
window.populateCategorySelect = populateCategorySelect; 