<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Choose you plan
        </h2>
        <p>Enjoy a monthly subscription with flexible plans</p>
    </x-slot>

    <div class="py-12">
        <div class="w-1/2 mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="p-6">
                        <form id="payment-form" action="{{ route('subscribe.store') }}" method="POST"
                              data-secret="{{ $intent->client_secret }}"
                        >
                            @csrf

                            @if(Session::has('success'))
                                <div
                                    class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800"
                                    role="alert">
                                    <span class="font-medium">Success!</span>
                                </div>
                            @endif

                            <div class="mb-4">
                                <h3 class="text-lg mb-4">
                                    Choose your plan
                                </h3>
                                <div class="">
                                    <!-- price_1KRmQcA5tIN4n0gQTEBGvfGz -- would be stored in your db -->
                                    <input type="radio" name="plan" id="standard"
                                           value="price_1KRmQcA5tIN4n0gQTEBGvfGz"
                                           checked
                                    >
                                    <label for="standard">Standard plan &pound;10.00 / month</label>
                                </div>
                                <div>
                                    <input type="radio" name="plan" id="premium"
                                           value="price_1KRbjPA5tIN4n0gQjBNt0zY8">
                                    <label for="premium">Premium plan &pound;59.00 / month</label>
                                </div>
                            </div>

                            <hr class="mb-4">

                            <h3 class="text-lg mb-4">
                                Credit or debit card
                            </h3>

                            <label for="card-holder-name">

                            </label>
                            <input type="text" id="card-holder-name" class="p-2 border mb-8"
                                   placeholder="Cardholder's name" value="{{ auth()->user()->name }}"
                            >

                            <div id="card-element">
                                <!-- Elements will create input elements here -->
                            </div>

                            <!-- We'll put the error messages in this element -->
                            <div id="card-errors" role="alert"></div>

                            <x-button class="mt-8" id="card-button">
                                Process payment
                            </x-button>

                            <svg id="spinner" role="status" class="mr-2 w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                            </svg>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="https://js.stripe.com/v3/"></script>

        <script>
            document.getElementById("spinner").style.visibility = 'hidden';

            var stripe = Stripe('{{ config('services.stripe.publishable') }}');
            var elements = stripe.elements();

            var style = {
                base: {
                    color: "#32325d",
                }
            };

            var card = elements.create('card', {style: style});

            card.mount('#card-element');

            card.on('change', function (event) {
                //console.log(event);
                var displayError = document.getElementById('card-errors');

                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            var form = document.getElementById('payment-form');
            var cardHolderName = document.getElementById('card-holder-name');
            var clientSecret = form.dataset.secret;

            form.addEventListener('submit', async function (ev) {
                ev.preventDefault();
                card.update({ disabled: true });
                document.getElementById("card-button").style.visibility = 'hidden';
                document.getElementById("spinner").style.visibility = 'visible';
                cardHolderName.readOnly = true;

                const {setupIntent, error} = await stripe.confirmCardSetup(
                    clientSecret, {
                        payment_method: {
                            card,
                            billing_details: {name: cardHolderName.value}
                        }
                    }
                );

                if (error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = error.message;
                } else {
                    var form = document.getElementById('payment-form');
                    var hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'paymentMethod');
                    // pass to our server
                    hiddenInput.setAttribute('value', setupIntent.payment_method);
                    form.appendChild(hiddenInput);
                    form.submit();
                }
            });
        </script>
    @endpush
</x-app-layout>
