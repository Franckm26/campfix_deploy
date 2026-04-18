/* Verify OTP Page JavaScript */
document.addEventListener('DOMContentLoaded', function() {
    const otpInputs = document.querySelectorAll('.otp-input');
    const hiddenOtp = document.getElementById('otp');
    const form = document.getElementById('otpForm');
    
    if (otpInputs.length > 0 && hiddenOtp) {
        // Combine OTP values
        function updateHiddenOtp() {
            let otp = '';
            otpInputs.forEach(input => {
                otp += input.value;
            });
            hiddenOtp.value = otp;
        }
        
        // Handle paste event - allow copy/paste of full OTP code
        otpInputs[0].addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text');
            // Extract only digits from pasted text
            const digits = pasteData.replace(/\D/g, '').split('').slice(0, 6);
            
            if (digits.length > 0) {
                digits.forEach((digit, index) => {
                    if (otpInputs[index]) {
                        otpInputs[index].value = digit;
                    }
                });
                // Focus the next empty input or the last input
                const nextEmptyIndex = digits.length < 6 ? digits.length : 5;
                otpInputs[nextEmptyIndex].focus();
                updateHiddenOtp();
                
                // Auto-submit if all 6 digits are filled
                if (digits.length === 6 && form) {
                    form.submit();
                }
            }
        });
        
        // Handle input
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                if (value.length === 1) {
                    // Move to next input
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                }
                updateHiddenOtp();
            });
            
            // Handle backspace
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && input.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
            
            // Handle Enter key to submit
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (hiddenOtp.value.length === 6 && form) {
                        form.submit();
                    }
                }
            });
            
            // Only allow numbers
            input.addEventListener('keypress', (e) => {
                if (!/^\d$/.test(e.key)) {
                    e.preventDefault();
                }
            });
        });
        
        // Auto-submit when all 6 digits are entered
        otpInputs.forEach(input => {
            input.addEventListener('input', () => {
                if (hiddenOtp.value.length === 6) {
                    // Optional: auto-submit
                    // document.getElementById('otpForm').submit();
                }
            });
        });
    }
});
