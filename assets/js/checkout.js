$(document).ready(function () {
    const paymentSection = $("#paymentSection");
    const paymentFields = $("#paymentFields");
    let verified = false;

    function showPaymentFields(method) {
        paymentFields.empty();
        verified = false;

        if (["bKash", "Nagad", "Rocket"].includes(method)) {
            paymentSection.removeClass("hidden");
            paymentFields.html(`
                <label class="block mb-1 font-medium">Mobile Number</label>
                <input type="text" id="mobileNumber" name="payment_number" placeholder="Enter ${method} number" class="w-full border rounded p-2 mb-2">
                <button type="button" id="sendCodeBtn" class="px-4 py-2 bg-green-600 text-white rounded mb-2">Send Code</button>
                <div id="verifySection" class="hidden mt-2">
                    <input type="text" id="verifyCodeInput" placeholder="Enter code" class="w-full border rounded p-2 mb-2">
                    <button type="button" id="verifyCodeBtn" class="px-4 py-2 bg-blue-600 text-white rounded">Verify</button>
                </div>
            `);
        } else if (["Visa", "MasterCard"].includes(method)) {
            paymentSection.removeClass("hidden");
            verified = true; // Card is automatically considered verified
            paymentFields.html(`
                <label class="block mb-1 font-medium">Card Number</label>
                <input type="text" id="cardNumber" name="payment_number" placeholder="XXXX-XXXX-XXXX-XXXX" class="w-full border rounded p-2 mb-2">
                <label class="block mb-1 font-medium">Expiry</label>
                <input type="text" id="expiryDate" name="expiry_date" placeholder="MM/YY" class="w-full border rounded p-2 mb-2">
                <label class="block mb-1 font-medium">CVV</label>
                <input type="password" id="cvv" name="cvv" placeholder="***" class="w-full border rounded p-2 mb-2">
                <div id="cardStatus" class="text-green-700 font-medium mt-1">✔ Card ready</div>
            `);
        } else {
            paymentSection.addClass("hidden");
        }
    }

    $(document).on('change', 'input[name="payment_method"]', function () {
        showPaymentFields($(this).val());
    });

    
    $(document).on("click", "#sendCodeBtn", function () {
        const number = $("#mobileNumber").val();
        if (!number) return alert("Please enter a mobile number.");

        $.post("checkout.php", { action: "send_code", number }, function (res) {
            const data = JSON.parse(res);
            if (data.success) {
                alert(data.message + " (Demo code: " + data.code + ")");
                $("#verifySection").removeClass("hidden");
            } else {
                alert("❌ " + data.message);
            }
        });
    });


    $(document).on("click", "#verifyCodeBtn", function () {
        const code = $("#verifyCodeInput").val();
        if (!code) return alert("Enter verification code.");

        $.post("checkout.php", { action: "verify_code", code }, function (res) {
            const data = JSON.parse(res);
            if (data.success) {
                verified = true;
                $("#verifySection").html(`<p class="text-green-700 font-medium">✔ Verified</p>`);
                alert("✅ Number verified successfully!");
            } else {
                alert("❌ " + data.message);
            }
        });
    });

    $("#checkoutForm").submit(function (e) {
        const method = $('input[name="payment_method"]:checked').val();
        if (!method) {
            alert("Please select a payment method.");
            e.preventDefault();
            return;
        }

        if (["bKash", "Nagad", "Rocket"].includes(method) && !verified) {
            alert("Please verify your mobile number before placing the order.");
            e.preventDefault();
            return;
        }

        if (["Visa", "MasterCard"].includes(method)) {
            const card = $("#cardNumber").val();
            if (!card) {
                alert("Please enter your card number.");
                e.preventDefault();
                return;
            }
        }
    });
});
