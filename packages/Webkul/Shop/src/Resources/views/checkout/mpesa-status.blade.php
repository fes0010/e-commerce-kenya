<x-shop::layouts
    :has-header="true"
    :has-feature="false"
    :has-footer="true"
>
    <!-- Page Title -->
    <x-slot:title>
        M-Pesa Payment Status
    </x-slot>

    <!-- Page content -->
    <div class="container mt-12 mb-20 px-[60px] max-lg:px-8 flex justify-center">
        <div class="bg-white shadow-[0_20px_50px_rgba(0,0,0,0.05)] rounded-[32px] p-10 max-w-lg w-full text-center border border-zinc-100/80 backdrop-blur-md">
            
            <!-- Status Icon Container -->
            <div class="relative flex justify-center items-center h-28 w-28 mx-auto mb-8">
                <!-- Spinner Wrapper -->
                <div id="status-spinner" class="absolute inset-0 flex items-center justify-center">
                    <svg class="animate-spin h-20 w-20 text-[#34A853]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Success Checkmark (Hidden initially) -->
                <div id="status-success" class="hidden absolute inset-0 flex items-center justify-center bg-green-50 rounded-full">
                    <svg class="h-16 w-16 text-green-600 scale-0 transition-transform duration-500 ease-out" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <!-- Failure Cross (Hidden initially) -->
                <div id="status-failed" class="hidden absolute inset-0 flex items-center justify-center bg-red-50 rounded-full">
                    <svg class="h-16 w-16 text-red-600 scale-0 transition-transform duration-500 ease-out" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>

            <!-- Header & Details -->
            <h2 id="status-title" class="text-2xl font-bold text-zinc-800 mb-3 tracking-tight">
                M-Pesa Payment Pending
            </h2>
            
            <p id="status-description" class="text-zinc-600 mb-6 leading-relaxed">
                An M-Pesa STK Push prompt has been sent to your phone <span class="font-semibold text-zinc-800">{{ $order->payment->additional['phone'] ?? '' }}</span>.<br>
                Please enter your M-Pesa PIN on your mobile device to authorize the payment of <strong>KES {{ number_format($order->grand_total, 2) }}</strong>.
            </p>

            <div id="status-meta" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-50 rounded-full text-sm text-zinc-500 mb-8 border border-zinc-100">
                <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                Order Ref: <span class="font-medium text-zinc-700">#{{ $order->increment_id }}</span>
            </div>

            <!-- Actions Container -->
            <div id="status-actions" class="flex flex-col gap-3 justify-center items-center">
                <p class="text-xs text-zinc-400">
                    Waiting for payment confirmation. Do not close this window.
                </p>
                
                <a
                    href="{{ route('mpesa.cancel', ['order_id' => $order->id]) }}"
                    id="btn-cancel"
                    class="mt-4 text-sm font-semibold text-zinc-400 hover:text-zinc-600 transition-colors"
                    onclick="if(typeof pollInterval!=='undefined'&&pollInterval){clearInterval(pollInterval);}"
                >
                    Cancel &amp; Return to Cart
                </a>
            </div>

            <!-- Retry Form Container (Hidden initially) -->
            <div id="retry-container" class="hidden mt-6 pt-6 border-t border-zinc-100 transition-all duration-300 ease-in-out">
                <form id="form-retry" class="flex flex-col gap-4 text-left">
                    <div>
                        <label for="retry-phone" class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-2">
                            M-Pesa Phone Number
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="retry-phone" 
                                name="phone" 
                                value="{{ $order->payment->additional['phone'] ?? '' }}" 
                                placeholder="e.g. 0712345678" 
                                class="w-full rounded-2xl border border-zinc-200 px-4 py-3.5 text-zinc-800 focus:outline-none focus:ring-2 focus:ring-[#34A853]/20 focus:border-[#34A853] transition-all duration-200 font-medium"
                                required
                            >
                        </div>
                        <p id="retry-error-msg" class="hidden text-xs text-red-500 mt-2 font-medium"></p>
                    </div>
                    
                    <button 
                        type="submit" 
                        id="btn-retry-submit" 
                        class="w-full cursor-pointer rounded-2xl bg-gradient-to-r from-[#34A853] to-[#2E8B57] hover:shadow-[0_8px_20px_rgba(52,168,83,0.3)] text-white px-6 py-3.5 text-center text-base font-bold transition-all duration-300 flex justify-center items-center gap-2 transform active:scale-[0.98]"
                    >
                        <span id="btn-text">Resend M-Pesa Prompt</span>
                        <svg id="btn-spinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Polling Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderId = "{{ $order->id }}";
            const checkStatusUrl = "{{ route('mpesa.check_status', ['order_id' => $order->id]) }}";
            const successRedirectUrl = "{{ route('shop.checkout.onepage.success') }}";
            const checkoutUrl = "{{ route('shop.checkout.onepage.index') }}";

            const spinner = document.getElementById('status-spinner');
            const successIcon = document.getElementById('status-success');
            const successSvg = successIcon.querySelector('svg');
            const failedIcon = document.getElementById('status-failed');
            const failedSvg = failedIcon.querySelector('svg');
            
            const statusTitle = document.getElementById('status-title');
            const statusDesc = document.getElementById('status-description');
            const statusActions = document.getElementById('status-actions');

            const retryContainer = document.getElementById('retry-container');
            const formRetry = document.getElementById('form-retry');
            const retryPhoneInput = document.getElementById('retry-phone');
            const retryErrorMsg = document.getElementById('retry-error-msg');
            const btnText = document.getElementById('btn-text');
            const btnSpinner = document.getElementById('btn-spinner');
            const btnRetrySubmit = document.getElementById('btn-retry-submit');

            let attempts = 0;
            const maxAttempts = 60; // 2 minutes total (2s interval)
            let pollInterval;
            const cancelUrl = "{{ route('mpesa.cancel', ['order_id' => $order->id]) }}";

            function stopPolling() {
                if (pollInterval) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                }
            }

            function startPolling() {
                attempts = 0;
                stopPolling();

                pollInterval = setInterval(function() {
                    attempts++;

                    if (attempts >= maxAttempts) {
                        stopPolling();
                        showFailed("Payment Timeout", "The payment request timed out. Please enter your number below to try again.");
                        return;
                    }

                    fetch(checkStatusUrl)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'completed') {
                                stopPolling();
                                showSuccess();
                            } else if (data.status === 'failed') {
                                stopPolling();
                                // Paynecta declined/cancelled — auto redirect to cart
                                showDeclined(data.message || "The payment was declined or cancelled.");
                            }
                        })
                        .catch(error => {
                            console.error('Error checking M-Pesa payment status:', error);
                        });
                }, 2000);
            }

            // Start polling initially
            startPolling();

            // Auto-cancel: called when Paynecta declines/cancels the payment.
            // The backend already marked the order as cancelled — just redirect to cart.
            function showDeclined(message) {
                // Swap spinner for cross icon
                spinner.classList.add('hidden');
                failedIcon.classList.remove('hidden');
                setTimeout(() => {
                    failedSvg.classList.remove('scale-0');
                    failedSvg.classList.add('scale-100');
                }, 50);

                statusTitle.innerText = "Payment Declined";
                statusTitle.classList.remove('text-green-600');
                statusTitle.classList.add('text-red-600');
                statusDesc.innerHTML = message + "<br>Redirecting you back to cart...";

                statusActions.innerHTML = '<p class="text-sm text-red-500 font-medium animate-pulse">Redirecting to cart...</p>';

                // Give the user 3 seconds to read the message then redirect
                setTimeout(function() {
                    window.location.href = cancelUrl;
                }, 3000);
            }

            function showSuccess() {
                // Hide spinner and failed if visible
                spinner.classList.add('hidden');
                failedIcon.classList.add('hidden');
                retryContainer.classList.add('hidden');
                
                // Show checkmark
                successIcon.classList.remove('hidden');
                setTimeout(() => {
                    successSvg.classList.remove('scale-0');
                    successSvg.classList.add('scale-100');
                }, 50);

                // Update text
                statusTitle.innerText = "Payment Successful!";
                statusTitle.classList.remove('text-red-600');
                statusTitle.classList.add('text-green-600');
                statusDesc.innerHTML = "Thank you! Your payment has been received successfully.<br>Redirecting to your order confirmation...";

                // Hide Action Buttons
                statusActions.innerHTML = '<p class="text-sm text-green-600 font-medium animate-pulse">Redirecting...</p>';

                // Redirect after 2.5 seconds
                setTimeout(function() {
                    window.location.href = successRedirectUrl;
                }, 2500);
            }

            function showFailed(title, message) {
                // Hide spinner
                spinner.classList.add('hidden');

                // Show cross
                failedIcon.classList.remove('hidden');
                setTimeout(() => {
                    failedSvg.classList.remove('scale-0');
                    failedSvg.classList.add('scale-100');
                }, 50);

                // Update text
                statusTitle.innerText = title;
                statusTitle.classList.remove('text-green-600');
                statusTitle.classList.add('text-red-600');
                statusDesc.innerHTML = message;

                // Show retry container
                retryContainer.classList.remove('hidden');

                // Update Action Buttons — stop polling on cancel click
                statusActions.innerHTML = `
                    <a
                        href="${cancelUrl}"
                        class="mt-2 text-sm font-semibold text-zinc-500 hover:text-zinc-700 transition-colors"
                        onclick="if(typeof pollInterval!=='undefined'&&pollInterval){clearInterval(pollInterval);}"
                    >
                        Cancel &amp; Return to Cart
                    </a>
                `;
            }

            // Handle Retry Form Submission
            formRetry.addEventListener('submit', function(e) {
                e.preventDefault();
                
                btnRetrySubmit.disabled = true;
                btnText.innerText = "Sending Prompt...";
                btnSpinner.classList.remove('hidden');
                retryErrorMsg.classList.add('hidden');

                const phone = retryPhoneInput.value;

                fetch("{{ route('mpesa.retry', ['order_id' => $order->id]) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ phone: phone })
                })
                .then(response => response.json())
                .then(data => {
                    btnRetrySubmit.disabled = false;
                    btnText.innerText = "Resend M-Pesa Prompt";
                    btnSpinner.classList.add('hidden');

                    if (data.success) {
                        retryContainer.classList.add('hidden');
                        failedIcon.classList.add('hidden');
                        failedSvg.classList.add('scale-0');
                        failedSvg.classList.remove('scale-100');

                        // Show spinner and reset texts
                        spinner.classList.remove('hidden');
                        statusTitle.innerText = "M-Pesa Payment Pending";
                        statusTitle.classList.remove('text-red-600');
                        statusDesc.innerHTML = `An M-Pesa STK Push prompt has been sent to your phone <span class="font-semibold text-zinc-800">${data.phone}</span>.<br>Please enter your M-Pesa PIN on your mobile device to authorize the payment of <strong>KES {{ number_format($order->grand_total, 2) }}</strong>.`;
                        
                        statusActions.innerHTML = `
                            <p class="text-xs text-zinc-400">
                                Waiting for payment confirmation. Do not close this window.
                            </p>
                            <a href="{{ route('mpesa.cancel', ['order_id' => $order->id]) }}" id="btn-cancel" class="mt-4 text-sm font-semibold text-zinc-400 hover:text-zinc-600 transition-colors">
                                Cancel & Return to Cart
                            </a>
                        `;

                        // Restart polling loop
                        startPolling();
                    } else {
                        retryErrorMsg.innerText = data.message || "Failed to trigger retry.";
                        retryErrorMsg.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    btnRetrySubmit.disabled = false;
                    btnText.innerText = "Resend M-Pesa Prompt";
                    btnSpinner.classList.add('hidden');
                    retryErrorMsg.innerText = "An error occurred. Please try again.";
                    retryErrorMsg.classList.remove('hidden');
                    console.error('Error retrying M-Pesa STK push:', error);
                });
            });
        });
    </script>
</x-shop::layouts>
