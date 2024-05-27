/** @type {import('tailwindcss').Config} */
export default {
    content: [
        // You will probably also need those lines
        "./resources/**/**/*.{js,blade.php}",
        "./app/View/Components/**/**/*.php",
        "./app/Livewire/**/**/*.php",
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        // Add mary
        "./vendor/robsontenorio/mary/src/View/Components/**/*.php",  
    ],
    theme: {
        extend: {
            colors: { 
                secondary: "#5c80bc",
            }, 
        },

    },
    daisyui: {
        themes: ["lemonade"]
    },
    plugins: [
        require("daisyui"),

    ],
}

