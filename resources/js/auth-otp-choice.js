/* OTP Choice Page JavaScript */
document.addEventListener('DOMContentLoaded', function() {
    const emailOption = document.getElementById('email-option');
    const phoneOption = document.getElementById('phone-option');
    
    if (emailOption) {
        const emailRadio = emailOption.querySelector('input[type="radio"]');
        
        // Set default selection
        if (emailRadio) {
            emailRadio.checked = true;
            emailOption.classList.add('selected');
        }
        
        function selectOption(option, radio) {
            emailOption.classList.remove('selected');
            if (phoneOption) {
                phoneOption.classList.remove('selected');
            }
            option.classList.add('selected');
            radio.checked = true;
        }
        
        emailOption.addEventListener('click', () => selectOption(emailOption, emailRadio));
        
        if (phoneOption) {
            const phoneRadio = phoneOption.querySelector('input[type="radio"]');
            phoneOption.addEventListener('click', () => selectOption(phoneOption, phoneRadio));
        }
    }
});
