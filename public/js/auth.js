/* Auth Pages JavaScript - OTP Verification */

document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll(".otp");

    if (inputs.length > 0) {
        // Focus first input on load
        inputs[0].focus();

        // Add focus styles
        inputs.forEach((input, index) => {
            
            input.addEventListener("input", (e) => {
                // Only allow numbers
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                
                if(input.value.length === 1 && inputs[index + 1]){
                    inputs[index + 1].focus();
                }
                
                updateOTP();
                
                // Add focus style
                input.style.borderColor = '#667eea';
                input.style.background = '#fff';
            });

            input.addEventListener("keydown", (e) => {
                if(e.key === "Backspace"){
                    if(!input.value && inputs[index - 1]){
                        inputs[index - 1].focus();
                    }
                }
            });
            
            input.addEventListener("focus", () => {
                input.style.borderColor = '#667eea';
                input.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';
            });
            
            input.addEventListener("blur", () => {
                input.style.borderColor = '#e5e7eb';
                input.style.boxShadow = 'none';
            });

        });

        // Update OTP hidden field
        function updateOTP(){
            let code = "";
            inputs.forEach(input=>{
                code += input.value;
            });
            const otpField = document.getElementById("otp");
            if (otpField) {
                otpField.value = code;
            }
        }

        // Prevent form submit if OTP is incomplete
        const otpForm = document.getElementById('otpForm');
        if (otpForm) {
            otpForm.addEventListener('submit', function(e) {
                const otp = document.getElementById('otp').value;
                if(otp.length !== 6) {
                    e.preventDefault();
                    alert('Please enter the complete 6-digit code');
                }
            });
        }
    }
});
