const providerPost = (url, body = null) => fetch(url, {
    method: 'POST',
    mode: 'cors',
    credentials: 'include',
    headers: body === null ? { Accept: 'application/json' } : {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
    body: body === null ? null : JSON.stringify(body),
});

const providerGet = (url) => fetch(url, {
    method: 'GET',
    mode: 'cors',
    credentials: 'include',
    headers: { Accept: 'application/json' },
});

const providerPatch = (url, body) => fetch(url, {
    method: 'PATCH',
    mode: 'cors',
    credentials: 'include',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
    body: JSON.stringify(body),
});

const providerPut = (url, body) => fetch(url, {
    method: 'PUT',
    mode: 'cors',
    credentials: 'include',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
    body: JSON.stringify(body),
});

const providerDelete = (url) => fetch(url, {
    method: 'DELETE',
    mode: 'cors',
    credentials: 'include',
    headers: { Accept: 'application/json' },
});

const wait = (milliseconds) => new Promise((resolve) => {
    setTimeout(resolve, milliseconds);
});

const customerSessionStorageKey = 'cue.customer';

const customerDisplayName = (customer) => customer?.firstName || customer?.name || customer?.email || '';

const customerValue = (customer, ...keys) => {
    for (const key of keys) {
        if (customer?.[key] !== undefined && customer[key] !== null && customer[key] !== '') {
            return customer[key];
        }
    }

    return '';
};

const storeCustomerSession = (customer) => {
    if (!('sessionStorage' in window)) {
        return;
    }

    if (customer === null || typeof customer !== 'object') {
        window.sessionStorage.removeItem(customerSessionStorageKey);

        return;
    }

    window.sessionStorage.setItem(customerSessionStorageKey, JSON.stringify({
        displayName: customerDisplayName(customer),
    }));
};

const storedCustomerSession = () => {
    if (!('sessionStorage' in window)) {
        return null;
    }

    try {
        return JSON.parse(window.sessionStorage.getItem(customerSessionStorageKey));
    } catch {
        return null;
    }
};

const applyCustomerSessionToBar = (customer) => {
    const bar = document.querySelector('[data-customer-session-bar]');

    if (bar === null) {
        return;
    }

    const loggedInContainer = bar.querySelector('[data-logged-in-container]');
    const loggedOutContainer = bar.querySelector('[data-logged-out-container]');
    const firstName = bar.querySelector('[data-logged-in-status-customer-first-name]');
    const displayName = customer?.displayName || customerDisplayName(customer);

    if (loggedInContainer === null || loggedOutContainer === null || firstName === null) {
        return;
    }

    if (displayName === '') {
        loggedInContainer.style.display = 'none';
        loggedOutContainer.style.display = 'inline-flex';

        return;
    }

    firstName.textContent = displayName;
    loggedOutContainer.style.display = 'none';
    loggedInContainer.style.display = 'inline-flex';
};

const refreshCustomerSessionBar = async () => {
    const bar = document.querySelector('[data-customer-session-bar]');

    if (bar === null) {
        return;
    }

    const storedCustomer = storedCustomerSession();

    if (storedCustomer !== null) {
        applyCustomerSessionToBar(storedCustomer);
    }

    for (const delay of [0, 350, 1000]) {
        if (delay > 0) {
            await wait(delay);
        }

        try {
            const response = await providerGet(bar.dataset.customerUrl);

            if (!response.ok) {
                throw new Error('Customer session unavailable.');
            }

            const customer = await response.json();
            storeCustomerSession(customer);
            applyCustomerSessionToBar(customer);

            return;
        } catch {
            // Retry briefly because Spektrix auth cookies can lag behind the login response.
        }
    }

    storeCustomerSession(null);
    applyCustomerSessionToBar(null);
};

void refreshCustomerSessionBar();

const redirectAuthenticatedLoginPage = async () => {
    const loginPage = document.querySelector('[data-customer-login-page]');

    if (loginPage === null || loginPage.dataset.customerUrl === undefined || loginPage.dataset.accountUrl === undefined) {
        return;
    }

    try {
        const response = await providerGet(loginPage.dataset.customerUrl);

        if (!response.ok) {
            return;
        }

        storeCustomerSession(await response.json());
        window.location.assign(loginPage.dataset.accountUrl);
    } catch {
        // Keep the login page available when the provider session cannot be confirmed.
    }
};

void redirectAuthenticatedLoginPage();

const textOrFallback = (value, fallback = 'Not supplied') => {
    if (value === null || value === undefined || value === '') {
        return fallback;
    }

    return String(value);
};

const customerMoneyLabel = (money) => {
    const amount = customerValue(money, 'amount', 'Amount');
    const currency = customerValue(money, 'currency', 'Currency');

    if (amount === '' && currency === '') {
        return '';
    }

    return [currency, amount].filter(Boolean).join(' ');
};

const customerDateInputValue = (value) => {
    if (typeof value !== 'string' || value === '') {
        return '';
    }

    return value.slice(0, 10);
};

const customerBooleanLabel = (value) => {
    if (value === true) {
        return 'Yes';
    }

    if (value === false) {
        return 'No';
    }

    return '';
};

const customerEndpoint = (baseUrl, path = '', query = {}) => {
    const url = new URL(baseUrl);
    url.pathname = `${url.pathname.replace(/\/$/, '')}${path}`;

    for (const [key, value] of Object.entries(query)) {
        if (value !== null && value !== undefined && value !== '') {
            url.searchParams.set(key, value);
        }
    }

    return url.toString();
};

const objectValue = (value, ...keys) => {
    for (const key of keys) {
        if (value?.[key] !== undefined && value[key] !== null && value[key] !== '') {
            return value[key];
        }
    }

    return null;
};

const countryCode = (country) => {
    if (typeof country === 'string') {
        return country;
    }

    return customerValue(country, 'isoCode', 'IsoCode');
};

const addressLine = (address) => [
    customerValue(address, 'name', 'Name'),
    customerValue(address, 'line1', 'Line1'),
    customerValue(address, 'line2', 'Line2'),
    customerValue(address, 'line3', 'Line3'),
    customerValue(address, 'line4', 'Line4'),
    customerValue(address, 'line5', 'Line5'),
    [customerValue(address, 'town', 'Town'), customerValue(address, 'postcode', 'Postcode')].filter(Boolean).join(', '),
    customerValue(customerValue(address, 'country', 'Country'), 'name', 'Name') || countryCode(customerValue(address, 'country', 'Country')),
].filter(Boolean);

const setText = (element, value, fallback = 'Not supplied') => {
    if (element !== null) {
        element.textContent = textOrFallback(value, fallback);
    }
};

const fillProfileForm = (form, customer) => {
    if (form === null) {
        return;
    }

    form.elements.namedItem('title').value = customerValue(customer, 'title', 'Title');
    form.elements.namedItem('firstName').value = customerValue(customer, 'firstName', 'FirstName');
    form.elements.namedItem('lastName').value = customerValue(customer, 'lastName', 'LastName');
    form.elements.namedItem('email').value = customerValue(customer, 'email', 'Email');
    form.elements.namedItem('phone').value = customerValue(customer, 'phone', 'Phone');
    form.elements.namedItem('mobile').value = customerValue(customer, 'mobile', 'Mobile');
    form.elements.namedItem('birthDate').value = customerDateInputValue(customerValue(customer, 'birthDate', 'BirthDate'));
    form.elements.namedItem('giftAidConfirmed').checked = customerValue(customer, 'giftAidConfirmed', 'GiftAidConfirmed') === true;
};

const toggleProfileEditMode = (account, isEditing) => {
    const summary = account.querySelector('[data-account-profile-summary]');
    const form = account.querySelector('[data-account-profile-form]');
    const editButton = account.querySelector('[data-account-profile-edit-button]');

    if (summary !== null) {
        summary.hidden = isEditing;
    }

    if (form !== null) {
        form.hidden = !isEditing;
    }

    if (editButton !== null) {
        editButton.hidden = isEditing;
    }
};

const renderAccountList = (container, items, emptyText, renderer) => {
    if (container === null) {
        return;
    }

    container.replaceChildren();

    if (!Array.isArray(items) || items.length === 0) {
        container.textContent = emptyText;

        return;
    }

    for (const item of items.slice(0, 6)) {
        container.appendChild(renderer(item));
    }
};

const accountCard = (...lines) => {
    const card = document.createElement('div');
    card.className = 'border border-[#171511]/10 bg-[#f5f0e8] p-4 text-sm leading-6 text-[#5d5549]';

    for (const line of lines.filter(Boolean)) {
        const paragraph = document.createElement('p');
        paragraph.textContent = line;
        card.appendChild(paragraph);
    }

    return card;
};

const renderCustomerAccount = (account, customer) => {
    const loading = account.querySelector('[data-account-loading]');
    const signedIn = account.querySelector('[data-account-signed-in]');
    const signedOut = account.querySelector('[data-account-signed-out]');
    const name = account.querySelector('[data-account-customer-name]');
    const title = account.querySelector('[data-account-customer-title]');
    const firstNameSummary = account.querySelector('[data-account-customer-first-name]');
    const lastNameSummary = account.querySelector('[data-account-customer-last-name]');
    const email = account.querySelector('[data-account-customer-email]');
    const phone = account.querySelector('[data-account-customer-phone]');
    const mobile = account.querySelector('[data-account-customer-mobile]');
    const birthDate = account.querySelector('[data-account-customer-birth-date]');
    const giftAid = account.querySelector('[data-account-customer-gift-aid]');
    const creditBalance = account.querySelector('[data-account-credit-balance]');
    const passwordState = account.querySelector('[data-account-password-state]');
    const profileForm = account.querySelector('[data-account-profile-form]');
    const recoveryEmail = account.querySelector('[data-account-password-recovery-form] input[name="emailAddress"]');

    const firstName = customerValue(customer, 'firstName', 'FirstName');
    const lastName = customerValue(customer, 'lastName', 'LastName');
    const displayName = [firstName, lastName].filter(Boolean).join(' ') || customerDisplayName(customer);

    loading.hidden = true;
    signedOut.hidden = true;
    signedIn.hidden = false;

    setText(name, displayName, 'Signed in');
    setText(title, customerValue(customer, 'title', 'Title'));
    setText(firstNameSummary, firstName);
    setText(lastNameSummary, lastName);
    setText(email, customerValue(customer, 'email', 'Email'));
    setText(phone, customerValue(customer, 'phone', 'Phone'));
    setText(mobile, customerValue(customer, 'mobile', 'Mobile'));
    setText(birthDate, customerDateInputValue(customerValue(customer, 'birthDate', 'BirthDate')));
    setText(giftAid, customerBooleanLabel(customerValue(customer, 'giftAidConfirmed', 'GiftAidConfirmed')));
    setText(creditBalance, customerMoneyLabel(customerValue(customer, 'creditBalance', 'CreditBalance')));
    setText(passwordState, customerValue(customer, 'passwordSet', 'PasswordSet') === false ? 'Password not set' : 'Password active');
    fillProfileForm(profileForm, customer);

    if (recoveryEmail !== null) {
        recoveryEmail.value = customerValue(customer, 'email', 'Email');
    }

    renderAccountList(
        account.querySelector('[data-account-addresses]'),
        customerValue(customer, 'addresses', 'Addresses'),
        'No saved addresses found.',
        (address) => accountCard(
            customerValue(address, 'line1', 'Line1'),
            [customerValue(address, 'town', 'Town'), customerValue(address, 'postTown', 'PostTown'), customerValue(address, 'postcode', 'Postcode')].filter(Boolean).join(', '),
            customerValue(address, 'country', 'Country'),
        ),
    );

    renderAccountList(
        account.querySelector('[data-account-orders]'),
        customerValue(customer, 'orders', 'Orders'),
        'No recent orders found.',
        (order) => accountCard(
            `Order ${textOrFallback(customerValue(order, 'friendlyId', 'FriendlyId', 'id', 'Id'), 'reference unavailable')}`,
            textOrFallback(customerValue(order, 'confirmedOn', 'ConfirmedOn'), 'Date unavailable'),
        ),
    );

    renderAccountList(
        account.querySelector('[data-account-stored-cards]'),
        customerValue(customer, 'storedCards', 'StoredCards'),
        'No stored cards found.',
        (card) => accountCard(
            [customerValue(card, 'cardType', 'CardType'), customerValue(card, 'lastFourDigits', 'LastFourDigits')].filter(Boolean).join(' ending '),
            textOrFallback(customerValue(card, 'expiryDate', 'ExpiryDate'), 'Expiry unavailable'),
        ),
    );

    renderContactPreferences(account, customer);
};

const renderContactPreferences = (account, customer) => {
    const container = account.querySelector('[data-account-contact-preferences]');

    if (container === null) {
        return;
    }

    const statements = customerValue(customer, 'allStatements', 'AllStatements') || customerValue(customer, 'agreedStatements', 'AgreedStatements');

    container.replaceChildren();

    if (!Array.isArray(statements) || statements.length === 0) {
        container.textContent = 'No contact preferences are currently available.';

        return;
    }

    for (const statement of statements) {
        const id = customerValue(statement, 'id', 'Id');

        if (id === '') {
            continue;
        }

        const label = document.createElement('label');
        label.className = 'flex gap-3 border border-[#171511]/10 bg-[#f5f0e8] p-4 text-sm leading-6 text-[#5d5549]';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'statementIds[]';
        checkbox.value = id;
        checkbox.checked = customerValue(statement, 'agreed', 'Agreed') === true;
        checkbox.className = 'mt-1 size-4 shrink-0 accent-[#a4432e]';

        const text = document.createElement('span');
        text.textContent = textOrFallback(customerValue(statement, 'text', 'Text'), 'Contact preference');

        label.append(checkbox, text);
        container.appendChild(label);
    }
};

const renderContactPreferenceStatements = (account, statements, agreedStatements) => {
    const container = account.querySelector('[data-account-contact-preferences]');

    if (container === null) {
        return;
    }

    const agreedIds = new Set((Array.isArray(agreedStatements) ? agreedStatements : [])
        .map((statement) => customerValue(statement, 'id', 'Id'))
        .filter(Boolean));

    container.dataset.agreedStatementIds = JSON.stringify([...agreedIds]);
    container.replaceChildren();

    if (!Array.isArray(statements) || statements.length === 0) {
        container.textContent = 'No contact preferences are currently available.';

        return;
    }

    for (const statement of statements) {
        const id = customerValue(statement, 'id', 'Id');

        if (id === '') {
            continue;
        }

        const label = document.createElement('label');
        label.className = 'flex gap-3 border border-[#171511]/10 bg-[#f5f0e8] p-4 text-sm leading-6 text-[#5d5549]';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'statementIds[]';
        checkbox.value = id;
        checkbox.checked = agreedIds.has(id) || customerValue(statement, 'agreed', 'Agreed') === true;
        checkbox.className = 'mt-1 size-4 shrink-0 accent-[#a4432e]';

        const text = document.createElement('span');
        text.textContent = textOrFallback(customerValue(statement, 'text', 'Text'), 'Contact preference');

        label.append(checkbox, text);
        container.appendChild(label);
    }
};

const fetchContactPreferences = async (account) => {
    const container = account.querySelector('[data-account-contact-preferences]');

    if (container === null || account.dataset.statementsUrl === undefined) {
        return;
    }

    try {
        const [statementsResponse, agreedResponse] = await Promise.all([
            providerGet(account.dataset.statementsUrl),
            providerGet(account.dataset.agreedStatementsUrl),
        ]);

        if (!statementsResponse.ok || !agreedResponse.ok) {
            throw new Error('Loading contact preferences failed.');
        }

        renderContactPreferenceStatements(account, await statementsResponse.json(), await agreedResponse.json());
    } catch {
        container.textContent = 'We could not load your contact preferences. Please try again later.';
    }
};

const addressPayloadFromForm = (form) => {
    const fields = new FormData(form);

    return {
        isDelivery: fields.get('isDelivery') === '1',
        isBilling: fields.get('isBilling') === '1',
        country: fields.get('country') || '',
        administrativeDivision: fields.get('administrativeDivision') || '',
        name: fields.get('name') || '',
        line1: fields.get('line1') || '',
        line2: fields.get('line2') || '',
        line3: fields.get('line3') || '',
        line4: fields.get('line4') || '',
        line5: fields.get('line5') || '',
        postcode: fields.get('postcode') || '',
        town: fields.get('town') || '',
    };
};

const populateAddressForm = (form, address = null) => {
    if (form === null) {
        return;
    }

    form.elements.namedItem('addressId').value = customerValue(address, 'id', 'Id');
    form.elements.namedItem('country').value = countryCode(customerValue(address, 'country', 'Country')) || 'GB';
    form.elements.namedItem('postcode').value = customerValue(address, 'postcode', 'Postcode');
    form.elements.namedItem('name').value = customerValue(address, 'name', 'Name');
    form.elements.namedItem('administrativeDivision').value = countryCode(customerValue(address, 'administrativeDivision', 'AdministrativeDivision'));
    form.elements.namedItem('line1').value = customerValue(address, 'line1', 'Line1');
    form.elements.namedItem('line2').value = customerValue(address, 'line2', 'Line2');
    form.elements.namedItem('line3').value = customerValue(address, 'line3', 'Line3');
    form.elements.namedItem('line4').value = customerValue(address, 'line4', 'Line4');
    form.elements.namedItem('line5').value = customerValue(address, 'line5', 'Line5');
    form.elements.namedItem('town').value = customerValue(address, 'town', 'Town');
    form.elements.namedItem('isBilling').checked = customerValue(address, 'isBilling', 'IsBilling') === true;
    form.elements.namedItem('isDelivery').checked = customerValue(address, 'isDelivery', 'IsDelivery') === true;
};

const showAddressForm = (account, address = null) => {
    const form = account.querySelector('[data-account-address-form]');
    const title = account.querySelector('[data-account-address-form-title]');
    const feedback = account.querySelector('[data-account-address-feedback]');
    const error = account.querySelector('[data-account-address-error]');

    if (form === null) {
        return;
    }

    populateAddressForm(form, address);
    form.hidden = false;
    setText(title, address === null ? 'Add address' : 'Edit address', 'Address');

    if (feedback !== null) {
        feedback.hidden = true;
    }

    if (error !== null) {
        error.hidden = true;
    }

    form.scrollIntoView({ block: 'start', behavior: 'smooth' });
};

const populateCountryOptions = async (account) => {
    const select = account.querySelector('[data-account-address-form] select[name="country"]');

    if (select === null || account.dataset.countriesLoaded === 'true') {
        return;
    }

    try {
        const response = await providerGet(account.dataset.countriesUrl);

        if (!response.ok) {
            throw new Error('Loading countries failed.');
        }

        const countries = await response.json();
        select.replaceChildren();

        for (const country of (Array.isArray(countries) ? countries : []).sort((left, right) => {
            const leftPriority = Number(customerValue(left, 'displayPriority', 'DisplayPriority') || 9999);
            const rightPriority = Number(customerValue(right, 'displayPriority', 'DisplayPriority') || 9999);

            return leftPriority - rightPriority || String(customerValue(left, 'name', 'Name')).localeCompare(String(customerValue(right, 'name', 'Name')));
        })) {
            const option = document.createElement('option');
            option.value = countryCode(country);
            option.textContent = customerValue(country, 'name', 'Name') || option.value;
            option.dataset.postcodeRequired = customerValue(country, 'postcodeRequired', 'PostcodeRequired') === true ? 'true' : 'false';
            select.appendChild(option);
        }

        account.dataset.countriesLoaded = 'true';
    } catch {
        const option = document.createElement('option');
        option.value = 'GB';
        option.textContent = 'United Kingdom';
        option.dataset.postcodeRequired = 'true';
        select.replaceChildren(option);
    }
};

const renderAddresses = (account, addresses) => {
    const container = account.querySelector('[data-account-addresses]');
    const status = account.querySelector('[data-account-addresses-status]');

    if (container === null) {
        return;
    }

    container.replaceChildren();

    if (!Array.isArray(addresses) || addresses.length === 0) {
        if (status !== null) {
            status.textContent = 'No saved addresses found.';
        }

        return;
    }

    if (status !== null) {
        status.textContent = '';
    }

    for (const address of addresses) {
        const card = accountCard(...addressLine(address));
        const badges = document.createElement('div');
        badges.className = 'mt-3 flex flex-wrap gap-2';

        for (const label of [
            customerValue(address, 'isBilling', 'IsBilling') === true ? 'Billing' : '',
            customerValue(address, 'isDelivery', 'IsDelivery') === true ? 'Delivery' : '',
        ].filter(Boolean)) {
            const badge = document.createElement('span');
            badge.className = 'border border-[#171511]/10 bg-white px-2 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-[#5d5549]';
            badge.textContent = label;
            badges.appendChild(badge);
        }

        const actions = document.createElement('div');
        actions.className = 'mt-4 flex flex-wrap gap-3';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'inline-flex min-h-10 items-center border border-[#171511]/15 px-3 text-sm font-semibold text-[#171511] hover:bg-white';
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', () => showAddressForm(account, address));

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'inline-flex min-h-10 items-center border border-[#a4432e]/30 px-3 text-sm font-semibold text-[#7b3021] hover:bg-white';
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', async () => {
            const id = customerValue(address, 'id', 'Id');

            if (id === '' || !window.confirm('Remove this address?')) {
                return;
            }

            deleteButton.disabled = true;

            try {
                const response = await providerDelete(customerEndpoint(account.dataset.addressesUrl, `/${encodeURIComponent(id)}`));

                if (!response.ok) {
                    throw new Error('Deleting address failed.');
                }

                await fetchAddresses(account);
            } catch {
                deleteButton.textContent = 'Could not delete';
            } finally {
                deleteButton.disabled = false;
            }
        });

        actions.append(editButton, deleteButton);
        card.append(badges, actions);
        container.appendChild(card);
    }
};

const fetchAddresses = async (account) => {
    const container = account.querySelector('[data-account-addresses]');
    const status = account.querySelector('[data-account-addresses-status]');

    if (container === null || account.dataset.addressesUrl === undefined) {
        return;
    }

    if (status !== null) {
        status.textContent = 'Loading saved addresses.';
    }

    try {
        await populateCountryOptions(account);
        const response = await providerGet(account.dataset.addressesUrl);

        if (!response.ok) {
            throw new Error('Loading addresses failed.');
        }

        renderAddresses(account, await response.json());
    } catch {
        if (status !== null) {
            status.textContent = 'We could not load your saved addresses. Please try again later.';
        }
    }
};

const orderReference = (order) => customerValue(order, 'friendlyId', 'FriendlyId', 'id', 'Id');

const renderOrderDetail = (detailContainer, order) => {
    detailContainer.replaceChildren();

    const groups = [
        ['Tickets', customerValue(order, 'tickets', 'Tickets'), (ticket) => [
            customerValue(ticket, 'event', 'Event')?.name || customerValue(ticket, 'event', 'Event')?.Name || customerValue(ticket, 'eventName', 'EventName'),
            customerValue(ticket, 'instance', 'Instance')?.start || customerValue(ticket, 'instance', 'Instance')?.Start,
            customerValue(ticket, 'seat', 'Seat')?.name || customerValue(ticket, 'seat', 'Seat')?.Name,
        ]],
        ['Deliveries', customerValue(order, 'deliveries', 'Deliveries'), (delivery) => [
            customerValue(delivery, 'type', 'Type')?.name || customerValue(delivery, 'type', 'Type')?.Name || customerValue(delivery, 'name', 'Name'),
            customerValue(delivery, 'address', 'Address') ? addressLine(customerValue(delivery, 'address', 'Address')).join(', ') : '',
        ]],
        ['Payments', customerValue(order, 'payments', 'Payments'), (payment) => [
            customerMoneyLabel(customerValue(payment, 'amount', 'Amount')) || customerValue(payment, 'amount', 'Amount'),
            customerValue(payment, 'method', 'Method')?.name || customerValue(payment, 'method', 'Method')?.Name,
        ]],
        ['Refunds', customerValue(order, 'refunds', 'Refunds'), (payment) => [
            customerMoneyLabel(customerValue(payment, 'amount', 'Amount')) || customerValue(payment, 'amount', 'Amount'),
            customerValue(payment, 'method', 'Method')?.name || customerValue(payment, 'method', 'Method')?.Name,
        ]],
        ['Charges', customerValue(order, 'charges', 'Charges'), (charge) => [
            customerValue(charge, 'name', 'Name'),
            customerMoneyLabel(customerValue(charge, 'amount', 'Amount')) || customerValue(charge, 'amount', 'Amount'),
        ]],
        ['Memberships', customerValue(order, 'membershipSubscriptions', 'MembershipSubscriptions'), (membership) => [
            customerValue(membership, 'name', 'Name'),
            customerValue(membership, 'membership', 'Membership')?.name || customerValue(membership, 'membership', 'Membership')?.Name,
        ]],
        ['Gift vouchers', customerValue(order, 'giftVouchers', 'GiftVouchers'), (voucher) => [
            customerValue(voucher, 'name', 'Name'),
            customerMoneyLabel(customerValue(voucher, 'value', 'Value')) || customerValue(voucher, 'value', 'Value'),
        ]],
        ['Donations', customerValue(order, 'donations', 'Donations'), (donation) => [
            customerValue(donation, 'fund', 'Fund')?.name || customerValue(donation, 'fund', 'Fund')?.Name,
            customerMoneyLabel(customerValue(donation, 'amount', 'Amount')) || customerValue(donation, 'amount', 'Amount'),
        ]],
        ['Merchandise', customerValue(order, 'merchandiseItems', 'MerchandiseItems'), (item) => [
            customerValue(item, 'name', 'Name'),
            customerMoneyLabel(customerValue(item, 'price', 'Price')) || customerValue(item, 'price', 'Price'),
        ]],
    ];

    for (const [heading, items, lineBuilder] of groups) {
        if (!Array.isArray(items) || items.length === 0) {
            continue;
        }

        const section = document.createElement('section');
        section.className = 'mt-4 border-t border-[#171511]/10 pt-4';

        const title = document.createElement('h4');
        title.className = 'text-sm font-semibold uppercase tracking-[0.14em] text-[#171511]';
        title.textContent = heading;
        section.appendChild(title);

        const list = document.createElement('div');
        list.className = 'mt-3 space-y-2';

        for (const item of items) {
            list.appendChild(accountCard(...lineBuilder(item).filter(Boolean)));
        }

        section.appendChild(list);
        detailContainer.appendChild(section);
    }

    if (detailContainer.children.length === 0) {
        detailContainer.textContent = 'No further order details are available.';
    }
};

const renderOrders = (account, orders) => {
    const container = account.querySelector('[data-account-orders]');
    const status = account.querySelector('[data-account-orders-status]');

    if (container === null) {
        return;
    }

    container.replaceChildren();

    if (!Array.isArray(orders) || orders.length === 0) {
        if (status !== null) {
            status.textContent = 'No recent orders found.';
        }

        return;
    }

    if (status !== null) {
        status.textContent = '';
    }

    for (const order of orders) {
        const id = customerValue(order, 'id', 'Id');
        const card = accountCard(
            `Order ${textOrFallback(orderReference(order), 'reference unavailable')}`,
            textOrFallback(customerValue(order, 'lastTransactionDate', 'LastTransactionDate', 'firstTransactionDate', 'FirstTransactionDate'), 'Date unavailable'),
            textOrFallback(customerMoneyLabel(customerValue(order, 'total', 'Total')) || customerValue(order, 'total', 'Total'), 'Total unavailable'),
        );
        const details = document.createElement('div');
        details.hidden = true;
        details.className = 'mt-4';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'mt-4 inline-flex min-h-10 items-center border border-[#171511]/15 px-3 text-sm font-semibold text-[#171511] hover:bg-white';
        button.textContent = 'View details';
        button.addEventListener('click', async () => {
            if (id === '') {
                return;
            }

            if (!details.hidden) {
                details.hidden = true;
                button.textContent = 'View details';

                return;
            }

            button.disabled = true;
            details.hidden = false;
            details.textContent = 'Loading order details.';

            try {
                const response = await providerGet(customerEndpoint(account.dataset.ordersUrl.replace('/customer/orders', '/orders'), `/${encodeURIComponent(id)}`));

                if (!response.ok) {
                    throw new Error('Loading order detail failed.');
                }

                renderOrderDetail(details, await response.json());
                button.textContent = 'Hide details';
            } catch {
                details.textContent = 'We could not load this order. Please try again later.';
            } finally {
                button.disabled = false;
            }
        });

        card.append(button, details);
        container.appendChild(card);
    }
};

const fetchOrders = async (account) => {
    const container = account.querySelector('[data-account-orders]');
    const status = account.querySelector('[data-account-orders-status]');

    if (container === null || account.dataset.ordersUrl === undefined) {
        return;
    }

    try {
        const response = await providerGet(account.dataset.ordersUrl);

        if (!response.ok) {
            throw new Error('Loading orders failed.');
        }

        renderOrders(account, await response.json());
    } catch {
        if (status !== null) {
            status.textContent = 'We could not load your order history. Please try again later.';
        }
    }
};

const renderPrintAtHomeDocuments = (account, documents) => {
    const container = account.querySelector('[data-account-print-at-home-documents]');
    const status = account.querySelector('[data-account-print-at-home-documents-status]');

    if (container === null) {
        return;
    }

    container.replaceChildren();

    if (!Array.isArray(documents) || documents.length === 0) {
        if (status !== null) {
            status.textContent = 'No e-tickets are currently available.';
        }

        return;
    }

    if (status !== null) {
        status.textContent = '';
    }

    for (const documentRecord of documents) {
        const id = customerValue(documentRecord, 'id', 'Id');
        const instance = customerValue(documentRecord, 'instance', 'Instance');
        const stockItem = customerValue(documentRecord, 'stockItem', 'StockItem');
        const card = accountCard(
            customerValue(stockItem, 'name', 'Name') || customerValue(instance, 'event', 'Event')?.name || customerValue(instance, 'event', 'Event')?.Name || 'E-ticket',
            customerValue(instance, 'start', 'Start') || customerValue(instance, 'startUtc', 'StartUtc'),
        );

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'mt-4 inline-flex min-h-10 items-center border border-[#171511]/15 px-3 text-sm font-semibold text-[#171511] hover:bg-white';
        button.textContent = 'Open e-ticket';
        button.addEventListener('click', async () => {
            if (id === '') {
                return;
            }

            button.disabled = true;

            try {
                const response = await providerGet(customerEndpoint(account.dataset.printAtHomeDocumentsUrl, `/${encodeURIComponent(id)}`));

                if (!response.ok && response.status !== 204) {
                    throw new Error('Opening e-ticket failed.');
                }

                window.open(customerEndpoint(account.dataset.printAtHomeDocumentsUrl, `/${encodeURIComponent(id)}`), '_blank', 'noopener');
            } catch {
                button.textContent = 'Could not open';
            } finally {
                button.disabled = false;
            }
        });

        card.appendChild(button);
        container.appendChild(card);
    }
};

const fetchPrintAtHomeDocuments = async (account) => {
    const container = account.querySelector('[data-account-print-at-home-documents]');
    const status = account.querySelector('[data-account-print-at-home-documents-status]');

    if (container === null || account.dataset.printAtHomeDocumentsUrl === undefined) {
        return;
    }

    const listUrl = account.dataset.printAtHomeDocumentsUrl.replace('/print-at-home-documents', '/customer/print-at-home-documents');

    try {
        const response = await providerGet(listUrl);

        if (!response.ok) {
            throw new Error('Loading e-tickets failed.');
        }

        renderPrintAtHomeDocuments(account, await response.json());
    } catch {
        if (status !== null) {
            status.textContent = 'We could not load your e-tickets. Please try again later.';
        }
    }
};

const renderStoredCards = (account, cards) => {
    const container = account.querySelector('[data-account-stored-cards]');
    const status = account.querySelector('[data-account-stored-cards-status]');

    if (container === null) {
        return;
    }

    container.replaceChildren();

    if (!Array.isArray(cards) || cards.length === 0) {
        if (status !== null) {
            status.textContent = 'No stored cards found.';
        }

        return;
    }

    if (status !== null) {
        status.textContent = '';
    }

    for (const cardRecord of cards) {
        const id = customerValue(cardRecord, 'id', 'Id');
        const card = accountCard(
            [customerValue(cardRecord, 'type', 'Type')?.name || customerValue(cardRecord, 'type', 'Type')?.Name, customerValue(cardRecord, 'maskedNumber', 'MaskedNumber')].filter(Boolean).join(' '),
            textOrFallback(customerValue(cardRecord, 'cardHolderName', 'CardHolderName'), 'Cardholder unavailable'),
            `Expires ${textOrFallback(customerValue(cardRecord, 'expiryDate', 'ExpiryDate'), 'unknown')}`,
            customerValue(cardRecord, 'isDefault', 'IsDefault') === true ? 'Default card' : '',
            customerValue(cardRecord, 'isPending', 'IsPending') === true ? 'Pending' : '',
        );

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'mt-4 inline-flex min-h-10 items-center border border-[#a4432e]/30 px-3 text-sm font-semibold text-[#7b3021] hover:bg-white';
        button.textContent = 'Remove card';
        button.addEventListener('click', async () => {
            if (id === '' || !window.confirm('Remove this stored card?')) {
                return;
            }

            button.disabled = true;

            try {
                const response = await providerDelete(customerEndpoint(account.dataset.storedCardsUrl, `/${encodeURIComponent(id)}`));

                if (!response.ok) {
                    throw new Error('Deleting stored card failed.');
                }

                if (response.status !== 204) {
                    renderStoredCards(account, await response.json());
                } else {
                    await fetchStoredCards(account);
                }
            } catch {
                button.textContent = 'Could not remove';
            } finally {
                button.disabled = false;
            }
        });

        card.appendChild(button);
        container.appendChild(card);
    }
};

const fetchStoredCards = async (account) => {
    const container = account.querySelector('[data-account-stored-cards]');
    const status = account.querySelector('[data-account-stored-cards-status]');

    if (container === null || account.dataset.storedCardsUrl === undefined) {
        return;
    }

    const includePending = account.querySelector('[data-account-stored-cards-include-pending]')?.checked === true ? 'true' : 'false';

    try {
        const response = await providerGet(customerEndpoint(account.dataset.storedCardsUrl, '', { includePending }));

        if (!response.ok) {
            throw new Error('Loading stored cards failed.');
        }

        renderStoredCards(account, await response.json());
    } catch {
        if (status !== null) {
            status.textContent = 'We could not load your stored cards. Please try again later.';
        }
    }
};

const hydrateCustomerAccount = async () => {
    const account = document.querySelector('[data-customer-account]');

    if (account === null) {
        return;
    }

    const loading = account.querySelector('[data-account-loading]');
    const signedIn = account.querySelector('[data-account-signed-in]');
    const signedOut = account.querySelector('[data-account-signed-out]');

    try {
        const response = await providerGet(account.dataset.customerUrl);

        if (!response.ok) {
            throw new Error('Customer session unavailable.');
        }

        const customer = await response.json();
        storeCustomerSession(customer);
        applyCustomerSessionToBar(customer);
        renderCustomerAccount(account, customer);
        await Promise.all([
            fetchContactPreferences(account),
            fetchAddresses(account),
            fetchOrders(account),
            fetchPrintAtHomeDocuments(account),
            fetchStoredCards(account),
        ]);
    } catch {
        storeCustomerSession(null);
        applyCustomerSessionToBar(null);

        loading.hidden = true;
        signedIn.hidden = true;
        signedOut.hidden = false;
    }
};

void hydrateCustomerAccount();

for (const button of document.querySelectorAll('[data-account-profile-edit-button]')) {
    button.addEventListener('click', () => {
        const account = button.closest('[data-customer-account]');

        toggleProfileEditMode(account, true);
    });
}

for (const button of document.querySelectorAll('[data-account-profile-cancel-button]')) {
    button.addEventListener('click', () => {
        const account = button.closest('[data-customer-account]');

        toggleProfileEditMode(account, false);
    });
}

for (const form of document.querySelectorAll('[data-account-profile-form]')) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const account = form.closest('[data-customer-account]');
        const fields = new FormData(form);
        const submitButton = form.querySelector('[data-account-profile-submit]');
        const feedback = form.querySelector('[data-account-profile-feedback]');
        const errorMessage = form.querySelector('[data-account-profile-error]');

        feedback.hidden = true;
        errorMessage.hidden = true;
        submitButton.disabled = true;

        try {
            const response = await providerPatch(account.dataset.updateCustomerUrl, {
                title: fields.get('title'),
                firstName: fields.get('firstName'),
                lastName: fields.get('lastName'),
                email: fields.get('email'),
                phone: fields.get('phone'),
                mobile: fields.get('mobile'),
                birthDate: fields.get('birthDate') || null,
                giftAidConfirmed: fields.get('giftAidConfirmed') === '1',
            });

            if (!response.ok) {
                throw new Error('Updating profile failed.');
            }

            const customer = {
                ...await response.json(),
                giftAidConfirmed: fields.get('giftAidConfirmed') === '1',
            };

            storeCustomerSession(customer);
            applyCustomerSessionToBar(customer);
            renderCustomerAccount(account, customer);
            toggleProfileEditMode(account, false);
            feedback.hidden = false;
            feedback.focus();
        } catch {
            errorMessage.hidden = false;
            errorMessage.focus();
        } finally {
            submitButton.disabled = false;
        }
    });
}

for (const form of document.querySelectorAll('[data-account-password-form]')) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const account = form.closest('[data-customer-account]');
        const submitButton = form.querySelector('[data-account-password-submit]');
        const feedback = form.querySelector('[data-account-password-feedback]');
        const errorMessage = form.querySelector('[data-account-password-error]');
        const oldPassword = form.elements.namedItem('oldPassword');
        const newPassword = form.elements.namedItem('newPassword');
        const confirmation = form.elements.namedItem('newPassword_confirmation');

        feedback.hidden = true;
        errorMessage.hidden = true;

        if (newPassword.value !== confirmation.value) {
            errorMessage.textContent = 'The two new passwords must match.';
            errorMessage.hidden = false;
            errorMessage.focus();

            return;
        }

        submitButton.disabled = true;

        try {
            const response = await providerPost(account.dataset.changePasswordUrl, {
                oldPassword: oldPassword.value,
                newPassword: newPassword.value,
            });

            if (!response.ok) {
                throw new Error('Changing password failed.');
            }

            form.reset();
            feedback.hidden = false;
            feedback.focus();
        } catch {
            errorMessage.textContent = 'We could not update your password. Please check your current password and try again.';
            errorMessage.hidden = false;
            errorMessage.focus();
        } finally {
            submitButton.disabled = false;
        }
    });
}

for (const form of document.querySelectorAll('[data-account-password-recovery-form]')) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const account = form.closest('[data-customer-account]');
        const submitButton = form.querySelector('[data-account-password-recovery-submit]');
        const feedback = form.querySelector('[data-account-password-recovery-feedback]');
        const errorMessage = form.querySelector('[data-account-password-recovery-error]');
        const emailAddress = form.elements.namedItem('emailAddress');
        const forgotPasswordUrl = new URL(account.dataset.forgotPasswordUrl);

        feedback.hidden = true;
        errorMessage.hidden = true;

        if (emailAddress.value.trim() === '') {
            errorMessage.textContent = 'Enter the email address for your ticketing account.';
            errorMessage.hidden = false;
            errorMessage.focus();

            return;
        }

        forgotPasswordUrl.searchParams.set('emailAddress', emailAddress.value.trim());
        forgotPasswordUrl.searchParams.set('domain', form.dataset.domain);
        submitButton.disabled = true;

        try {
            const response = await providerPost(forgotPasswordUrl.toString());

            if (!response.ok) {
                throw new Error('Requesting password reset failed.');
            }

            try {
                await providerPost(account.dataset.deauthenticateUrl);
            } catch {
                // The reset email has already been requested; continue the recovery journey.
            }

            storeCustomerSession(null);
            window.location.assign(account.dataset.passwordResetRequestedUrl);
        } catch {
            errorMessage.textContent = 'We could not send a reset link. Please check your email address and try again.';
            errorMessage.hidden = false;
            errorMessage.focus();
        } finally {
            submitButton.disabled = false;
        }
    });
}

for (const button of document.querySelectorAll('[data-account-address-new-button]')) {
    button.addEventListener('click', async () => {
        const account = button.closest('[data-customer-account]');

        await populateCountryOptions(account);
        showAddressForm(account);
    });
}

for (const button of document.querySelectorAll('[data-account-address-cancel-button]')) {
    button.addEventListener('click', () => {
        const form = button.closest('[data-account-address-form]');

        if (form !== null) {
            form.hidden = true;
        }
    });
}

for (const button of document.querySelectorAll('[data-account-address-postcode-button]')) {
    button.addEventListener('click', async () => {
        const form = button.closest('[data-account-address-form]');
        const account = button.closest('[data-customer-account]');
        const results = form.querySelector('[data-account-address-postcode-results]');
        const postcode = form.elements.namedItem('postcode').value.trim();

        results.replaceChildren();

        if (postcode === '') {
            results.textContent = 'Enter a postcode to search.';

            return;
        }

        button.disabled = true;

        try {
            const response = await providerGet(customerEndpoint(account.dataset.postcodeLookupUrl, '', { postcode }));

            if (!response.ok) {
                throw new Error('Postcode lookup failed.');
            }

            const matches = await response.json();

            if (!Array.isArray(matches) || matches.length === 0) {
                results.textContent = 'No matching addresses found.';

                return;
            }

            for (const match of matches) {
                const id = customerValue(match, 'id', 'Id');
                const resultButton = document.createElement('button');
                resultButton.type = 'button';
                resultButton.className = 'block w-full border border-[#171511]/10 bg-[#f5f0e8] px-4 py-3 text-left text-sm leading-6 text-[#171511] hover:bg-white';
                resultButton.textContent = textOrFallback(customerValue(match, 'description', 'Description'), 'Address result');
                resultButton.addEventListener('click', async () => {
                    if (id === '') {
                        return;
                    }

                    resultButton.disabled = true;

                    try {
                        const detailResponse = await providerGet(customerEndpoint(account.dataset.postcodeLookupUrl, `/${encodeURIComponent(id)}`));

                        if (!detailResponse.ok) {
                            throw new Error('Postcode address detail failed.');
                        }

                        const address = await detailResponse.json();
                        populateAddressForm(form, {
                            ...address,
                            id: form.elements.namedItem('addressId').value,
                            isBilling: form.elements.namedItem('isBilling').checked,
                            isDelivery: form.elements.namedItem('isDelivery').checked,
                        });
                        results.replaceChildren();
                    } catch {
                        results.textContent = 'We could not load that address. Please enter it manually.';
                    } finally {
                        resultButton.disabled = false;
                    }
                });
                results.appendChild(resultButton);
            }
        } catch {
            results.textContent = 'We could not search for that postcode. Please enter the address manually.';
        } finally {
            button.disabled = false;
        }
    });
}

for (const form of document.querySelectorAll('[data-account-address-form]')) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const account = form.closest('[data-customer-account]');
        const submitButton = form.querySelector('[data-account-address-submit]');
        const feedback = form.querySelector('[data-account-address-feedback]');
        const errorMessage = form.querySelector('[data-account-address-error]');
        const payload = addressPayloadFromForm(form);
        const addressId = form.elements.namedItem('addressId').value;
        const selectedCountry = form.elements.namedItem('country').selectedOptions[0];

        feedback.hidden = true;
        errorMessage.hidden = true;

        if (payload.line1.trim() === '' || payload.town.trim() === '' || payload.country.trim() === '') {
            errorMessage.textContent = 'Enter at least address line 1, town or city, and country.';
            errorMessage.hidden = false;
            errorMessage.focus();

            return;
        }

        if (selectedCountry?.dataset.postcodeRequired === 'true' && payload.postcode.trim() === '') {
            errorMessage.textContent = 'Enter a postcode for the selected country.';
            errorMessage.hidden = false;
            errorMessage.focus();

            return;
        }

        submitButton.disabled = true;

        try {
            const response = addressId === ''
                ? await providerPost(account.dataset.addressesUrl, payload)
                : await providerPatch(customerEndpoint(account.dataset.addressesUrl, `/${encodeURIComponent(addressId)}`), payload);

            if (!response.ok) {
                throw new Error('Saving address failed.');
            }

            await fetchAddresses(account);
            form.hidden = true;
            feedback.hidden = false;
            feedback.focus();
        } catch {
            errorMessage.textContent = 'We could not save this address. Please check the details and try again.';
            errorMessage.hidden = false;
            errorMessage.focus();
        } finally {
            submitButton.disabled = false;
        }
    });
}

for (const checkbox of document.querySelectorAll('[data-account-stored-cards-include-pending]')) {
    checkbox.addEventListener('change', async () => {
        const account = checkbox.closest('[data-customer-account]');

        await fetchStoredCards(account);
    });
}

for (const form of document.querySelectorAll('[data-account-contact-preferences-form]')) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const account = form.closest('[data-customer-account]');
        const container = account.querySelector('[data-account-contact-preferences]');
        const submitButton = form.querySelector('[data-account-contact-preferences-submit]');
        const feedback = form.querySelector('[data-account-contact-preferences-feedback]');
        const errorMessage = form.querySelector('[data-account-contact-preferences-error]');
        const selectedIds = new Set([...form.querySelectorAll('input[name="statementIds[]"]:checked')]
            .map((checkbox) => checkbox.value));
        const originalIds = new Set(JSON.parse(container?.dataset.agreedStatementIds || '[]'));
        const addedIds = [...selectedIds].filter((id) => !originalIds.has(id));
        const removedIds = [...originalIds].filter((id) => !selectedIds.has(id));

        feedback.hidden = true;
        errorMessage.hidden = true;
        submitButton.disabled = true;

        try {
            if (addedIds.length > 0) {
                const response = await providerPost(account.dataset.agreedStatementsUrl, addedIds.map((id) => ({ id })));

                if (!response.ok) {
                    throw new Error('Adding contact preferences failed.');
                }
            }

            for (const id of removedIds) {
                const response = await providerDelete(customerEndpoint(account.dataset.agreedStatementsUrl, `/${encodeURIComponent(id)}`));

                if (!response.ok) {
                    throw new Error('Removing contact preferences failed.');
                }
            }

            await fetchContactPreferences(account);
            feedback.hidden = false;
            feedback.focus();
        } catch {
            errorMessage.hidden = false;
            errorMessage.focus();
        } finally {
            submitButton.disabled = false;
        }
    });
}

for (const form of document.querySelectorAll('[data-customer-registration-form]')) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitButton = form.querySelector('[data-customer-registration-submit]');
        const errorMessage = form.querySelector('[data-customer-registration-error]');
        const password = form.elements.namedItem('password');
        const confirmation = form.elements.namedItem('password_confirmation');
        const fields = new FormData(form);
        const url = new URL(form.dataset.createCustomerUrl);

        errorMessage.hidden = true;

        if (password.value !== confirmation.value) {
            errorMessage.textContent = 'The two passwords must match.';
            errorMessage.hidden = false;
            errorMessage.focus();

            return;
        }

        url.searchParams.set('domain', form.dataset.domain);
        submitButton.disabled = true;

        try {
            const response = await providerPost(url.toString(), {
                firstName: fields.get('firstName'),
                lastName: fields.get('lastName'),
                email: fields.get('email'),
                password: fields.get('password'),
            });

            if (!response.ok) {
                throw new Error('Creating a customer failed.');
            }

            window.location.assign(form.dataset.successUrl);
        } catch {
            errorMessage.textContent = 'We could not create your account. Check your details or try logging in if you already have an account.';
            errorMessage.hidden = false;
            errorMessage.focus();
            submitButton.disabled = false;
        }
    });
}

for (const form of document.querySelectorAll('[data-customer-login-form]')) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitButton = form.querySelector('[data-customer-login-submit]');
        const errorMessage = form.querySelector('[data-customer-login-error]');
        const statusMessage = form.querySelector('[data-customer-login-status]');
        const password = form.elements.namedItem('password');
        const fields = new FormData(form);

        errorMessage.hidden = true;
        statusMessage.textContent = 'Signing you in securely.';
        submitButton.disabled = true;

        try {
            const response = await providerPost(form.dataset.authenticateUrl, {
                email: fields.get('email'),
                password: fields.get('password'),
            });

            if (!response.ok) {
                throw new Error('Customer authentication failed.');
            }

            storeCustomerSession(await response.json());
            statusMessage.textContent = 'Login successful. Returning to events.';
            window.location.assign(form.dataset.successUrl);
        } catch {
            errorMessage.hidden = false;
            errorMessage.focus();
            statusMessage.textContent = '';
            submitButton.disabled = false;

            if (password instanceof HTMLInputElement) {
                password.value = '';
            }
        }
    });
}

for (const button of document.querySelectorAll('[data-customer-logout-button]')) {
    button.addEventListener('click', async () => {
        button.disabled = true;

        try {
            const response = await providerPost(button.dataset.deauthenticateUrl);

            if (!response.ok) {
                throw new Error('Customer logout failed.');
            }

            storeCustomerSession(null);
            window.location.assign(button.dataset.successUrl);
        } catch {
            button.disabled = false;
        }
    });
}

for (const form of document.querySelectorAll('[data-customer-magic-link-request-form]')) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitButton = form.querySelector('[data-customer-magic-link-submit]');
        const feedback = form.querySelector('[data-customer-magic-link-feedback]');
        const errorMessage = form.querySelector('[data-customer-magic-link-error]');
        const fields = new FormData(form);

        feedback.hidden = true;
        errorMessage.hidden = true;
        submitButton.disabled = true;

        try {
            const response = await providerPost(form.dataset.sendMagicLinkUrl, {
                emailAddress: fields.get('emailAddress'),
                linkUrl: form.dataset.linkUrl,
            });

            if (!response.ok) {
                throw new Error('Sending a magic link failed.');
            }

            feedback.hidden = false;
            feedback.focus();
        } catch {
            errorMessage.hidden = false;
            errorMessage.focus();
        } finally {
            submitButton.disabled = false;
        }
    });
}

// Basket page

const formatBasketMoney = (amount) => {
    if (amount === null || amount === undefined || amount === '') {
        return '';
    }

    const num = Number(amount);

    if (Number.isNaN(num)) {
        return String(amount);
    }

    return `£${num.toFixed(2)}`;
};

const basketVal = (obj, key) => {
    const value = obj?.[key] ?? obj?.[key.charAt(0).toUpperCase() + key.slice(1)];

    return value !== undefined && value !== null ? value : null;
};

const renderBasketSavings = (basket, section) => {
    const savingsEl = section.querySelector('[data-basket-savings]');
    const offersEl = section.querySelector('[data-basket-offers]');
    // Pre-fill the promo input if a code is already applied (it lives outside this section).
    const promoInput = section.querySelector('[name="promoCode"]');
    const promoCode = basketVal(basket, 'promoCode');

    if (promoInput !== null && typeof promoCode === 'string' && promoCode !== '') {
        promoInput.value = promoCode;
    }

    if (savingsEl === null) {
        return;
    }

    const potentialOffers = basketVal(basket, 'potentialOffers') || [];
    const multibuyOffers = basketVal(basket, 'multibuyOffers') || [];
    const appliedOffers = basketVal(basket, 'offers') || [];
    const hasOffers = (Array.isArray(potentialOffers) && potentialOffers.length > 0)
        || (Array.isArray(multibuyOffers) && multibuyOffers.length > 0)
        || (Array.isArray(appliedOffers) && appliedOffers.length > 0);

    if (!hasOffers) {
        savingsEl.hidden = true;

        return;
    }

    savingsEl.hidden = false;

    if (offersEl === null) {
        return;
    }

    offersEl.replaceChildren();

    if (Array.isArray(appliedOffers) && appliedOffers.length > 0) {
        const appliedHeading = document.createElement('p');
        appliedHeading.className = 'font-medium text-[#171511]';
        appliedHeading.textContent = 'Applied offers';
        offersEl.appendChild(appliedHeading);

        for (const offer of appliedOffers) {
            const p = document.createElement('p');
            p.textContent = basketVal(offer, 'name') || 'Offer applied';
            offersEl.appendChild(p);
        }
    }

    if (Array.isArray(potentialOffers) && potentialOffers.length > 0) {
        const potentialHeading = document.createElement('p');
        potentialHeading.className = 'mt-3 font-medium text-[#171511]';
        potentialHeading.textContent = 'Add more tickets to qualify for';
        offersEl.appendChild(potentialHeading);

        for (const offer of potentialOffers) {
            const p = document.createElement('p');
            p.textContent = basketVal(offer, 'name') || 'Available offer';
            offersEl.appendChild(p);
        }
    }
};

const renderBasketMembershipUpsell = (basket, section) => {
    const upsell = section.querySelector('[data-basket-membership-upsell]');
    const copy = section.querySelector('[data-basket-membership-upsell-copy]');
    const loginLink = section.querySelector('[data-basket-login-url]');

    if (upsell === null) {
        return;
    }

    const customer = basketVal(basket, 'customer');
    const isLoggedIn = customer !== null && typeof customer === 'object' && basketVal(customer, 'id') !== null;

    if (isLoggedIn) {
        upsell.hidden = true;

        return;
    }

    upsell.hidden = false;

    if (copy !== null) {
        copy.textContent = section.dataset.membershipUpsell || '';
    }

    if (loginLink !== null) {
        loginLink.href = section.dataset.loginUrl || '#';
    }
};

const enrichMembershipUpsell = async (basket, section) => {
    const customer = basketVal(basket, 'customer');
    const isLoggedIn = customer !== null && typeof customer === 'object' && basketVal(customer, 'id') !== null;
    const copy = section.querySelector('[data-basket-membership-upsell-copy]');

    if (isLoggedIn || copy === null) {
        return;
    }

    const membershipsUrl = section.dataset.membershipsUrl;
    const potentialDiscountUrl = section.dataset.basketPotentialDiscountUrl;

    if (membershipsUrl === undefined || potentialDiscountUrl === undefined) {
        return;
    }

    try {
        const membershipsResponse = await providerGet(membershipsUrl);

        if (!membershipsResponse.ok) {
            return;
        }

        const memberships = await membershipsResponse.json();

        if (!Array.isArray(memberships) || memberships.length === 0) {
            return;
        }

        const requests = memberships
            .map((membership) => basketVal(membership, 'id'))
            .filter((id) => typeof id === 'string' && id !== '')
            .map((membershipId) => {
                const url = new URL(potentialDiscountUrl);
                url.searchParams.set('membershipId', membershipId);

                return providerGet(url.toString())
                    .then(async (response) => (response.ok ? response.json() : null))
                    .then((payload) => ({
                        membershipName: basketVal(memberships.find((membership) => basketVal(membership, 'id') === membershipId), 'name') || 'membership',
                        totalDiscount: Number(basketVal(payload, 'totalDiscount') || 0),
                    }))
                    .catch(() => null);
            });

        const results = (await Promise.all(requests)).filter((result) => result !== null);

        if (results.length === 0) {
            return;
        }

        results.sort((left, right) => right.totalDiscount - left.totalDiscount);
        const best = results[0];

        if (best.totalDiscount > 0) {
            copy.textContent = `Join ${best.membershipName} and save ${formatBasketMoney(best.totalDiscount)} on this order.`;
        }
    } catch {
        // Keep static copy on provider failures.
    }
};

const renderBasketTickets = (basket, section) => {
    const container = section.querySelector('[data-basket-tickets]');

    if (container === null) {
        return;
    }

    const tickets = basketVal(basket, 'tickets') || [];
    container.replaceChildren();

    if (!Array.isArray(tickets) || tickets.length === 0) {
        const p = document.createElement('p');
        p.className = 'text-sm text-[#5d5549]';
        p.textContent = 'No tickets in basket.';
        container.appendChild(p);

        return;
    }

    for (const ticket of tickets) {
        const id = basketVal(ticket, 'id');
        const event = basketVal(ticket, 'event');
        const instance = basketVal(ticket, 'instance');
        const ticketType = basketVal(ticket, 'type') || basketVal(ticket, 'ticketType');
        const total = basketVal(ticket, 'total');
        const discount = basketVal(ticket, 'discount');
        const offer = basketVal(ticket, 'offer');

        const eventName = basketVal(event, 'name') || 'Event';
        const instanceStart = basketVal(instance, 'start') || basketVal(instance, 'startUtc') || '';
        const typeName = basketVal(ticketType, 'name') || 'Ticket';

        const card = document.createElement('div');
        card.className = 'border border-[#171511]/10 bg-white p-5';

        const header = document.createElement('div');
        header.className = 'flex items-start justify-between gap-4';

        const info = document.createElement('div');
        info.className = 'min-w-0';

        const nameEl = document.createElement('p');
        nameEl.className = 'font-semibold text-[#171511] truncate';
        nameEl.textContent = eventName;
        info.appendChild(nameEl);

        if (instanceStart !== '') {
            const dateEl = document.createElement('p');
            dateEl.className = 'mt-1 text-sm text-[#5d5549]';
            dateEl.textContent = instanceStart;
            info.appendChild(dateEl);
        }

        const typeEl = document.createElement('p');
        typeEl.className = 'mt-1 text-sm text-[#5d5549]';
        typeEl.textContent = typeName;
        info.appendChild(typeEl);

        if (offer !== null && basketVal(offer, 'name') !== null) {
            const offerEl = document.createElement('p');
            offerEl.className = 'mt-1 text-xs text-[#a4432e]';
            offerEl.textContent = basketVal(offer, 'name') || '';
            info.appendChild(offerEl);
        }

        const priceEl = document.createElement('div');
        priceEl.className = 'shrink-0 text-right';

        const totalEl = document.createElement('p');
        totalEl.className = 'font-semibold text-[#171511]';
        totalEl.textContent = total !== null ? formatBasketMoney(total) : '';
        priceEl.appendChild(totalEl);

        if (discount !== null && Number(discount) > 0) {
            const discountEl = document.createElement('p');
            discountEl.className = 'mt-1 text-xs text-[#a4432e]';
            discountEl.textContent = `Saving ${formatBasketMoney(discount)}`;
            priceEl.appendChild(discountEl);
        }

        header.append(info, priceEl);
        card.appendChild(header);

        if (id !== null) {
            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'mt-4 inline-flex min-h-10 items-center border border-[#a4432e]/30 px-3 text-sm font-semibold text-[#7b3021] transition hover:bg-[#f5f0e8] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e] disabled:opacity-60';
            deleteButton.textContent = 'Remove';
            deleteButton.addEventListener('click', async () => {
                if (!window.confirm('Remove this ticket from your basket?')) {
                    return;
                }

                deleteButton.disabled = true;

                const url = new URL(section.dataset.basketTicketsUrl);
                url.searchParams.append('ticketIds[]', id);

                try {
                    const response = await providerDelete(url.toString());

                    if (!response.ok && response.status !== 204) {
                        throw new Error('Ticket removal failed.');
                    }

                    await reloadBasket(section);
                } catch {
                    deleteButton.textContent = 'Could not remove';
                    deleteButton.disabled = false;
                }
            });
            card.appendChild(deleteButton);
        }

        container.appendChild(card);
    }
};

const renderBasketTotals = (basket, section) => {
    const container = section.querySelector('[data-basket-totals]');

    if (container === null) {
        return;
    }

    container.replaceChildren();

    const total = basketVal(basket, 'total');
    const totalDiscount = basketVal(basket, 'totalDiscount');
    const charges = basketVal(basket, 'charges') || [];

    const rows = [];

    if (Array.isArray(charges)) {
        for (const charge of charges) {
            const name = basketVal(charge, 'name') || basketVal(charge, 'description') || 'Charge';
            const amount = basketVal(charge, 'amount') || basketVal(charge, 'total');

            if (amount !== null) {
                rows.push([name, formatBasketMoney(amount)]);
            }
        }
    }

    if (totalDiscount !== null && Number(totalDiscount) > 0) {
        rows.push(['Total savings', `-${formatBasketMoney(totalDiscount)}`]);
    }

    for (const [label, value] of rows) {
        const row = document.createElement('div');
        row.className = 'flex justify-between py-1';

        const labelEl = document.createElement('span');
        labelEl.textContent = label;
        const valueEl = document.createElement('span');
        valueEl.textContent = value;

        row.append(labelEl, valueEl);
        container.appendChild(row);
    }

    if (total !== null) {
        const totalRow = document.createElement('div');
        totalRow.className = 'mt-3 flex justify-between border-t border-[#171511]/10 pt-3 font-semibold text-[#171511]';

        const totalLabel = document.createElement('span');
        totalLabel.textContent = 'Total';
        const totalValue = document.createElement('span');
        totalValue.textContent = formatBasketMoney(total);

        totalRow.append(totalLabel, totalValue);
        container.appendChild(totalRow);
    }
};

const renderBasketMerchandise = (stockItems, section) => {
    const merchandiseSection = section.querySelector('[data-basket-merchandise]');
    const container = section.querySelector('[data-basket-merchandise-items]');

    if (merchandiseSection === null || container === null) {
        return;
    }

    const available = Array.isArray(stockItems)
        ? stockItems.filter((item) => (basketVal(item, 'stockLevel') ?? 1) > 0)
        : [];

    if (available.length === 0) {
        merchandiseSection.hidden = true;

        return;
    }

    merchandiseSection.hidden = false;
    container.replaceChildren();

    const clientName = section.dataset.clientName || '';
    const customDomain = section.dataset.customDomain || '';

    for (const item of available) {
        const id = basketVal(item, 'id');
        const name = basketVal(item, 'name') || 'Merchandise';
        const price = basketVal(item, 'price');
        const imageUrl = basketVal(item, 'imageUrl') || basketVal(item, 'thumbnailUrl');

        if (id === null) {
            continue;
        }

        const card = document.createElement('div');
        card.className = 'border border-[#171511]/10 bg-white p-4';

        if (imageUrl !== null) {
            const img = document.createElement('img');
            img.src = imageUrl;
            img.alt = name;
            img.className = 'mb-3 h-32 w-full object-cover';
            img.loading = 'lazy';
            card.appendChild(img);
        }

        const nameEl = document.createElement('p');
        nameEl.className = 'text-sm font-semibold text-[#171511]';
        nameEl.textContent = name;
        card.appendChild(nameEl);

        if (price !== null) {
            const priceEl = document.createElement('p');
            priceEl.className = 'mt-1 text-sm text-[#5d5549]';
            priceEl.textContent = formatBasketMoney(price);
            card.appendChild(priceEl);
        }

        const component = document.createElement('spektrix-merchandise');
        component.setAttribute('client-name', clientName);
        component.setAttribute('merchandise-item-id', id);

        if (customDomain !== '') {
            component.setAttribute('custom-domain', customDomain);
        }

        const submitButton = document.createElement('button');
        submitButton.setAttribute('data-submit-merchandise', '');
        submitButton.className = 'mt-3 inline-flex min-h-10 w-full items-center justify-center bg-[#a4432e] px-4 text-sm font-semibold text-white transition hover:bg-[#873625] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#a4432e]';
        submitButton.textContent = 'Add to basket';

        const successEl = document.createElement('div');
        successEl.setAttribute('data-success-container', '');
        successEl.style.display = 'none';
        successEl.className = 'mt-2 text-sm text-[#5d5549]';
        successEl.textContent = 'Added to your basket.';

        const failEl = document.createElement('div');
        failEl.setAttribute('data-fail-container', '');
        failEl.style.display = 'none';
        failEl.className = 'mt-2 text-sm text-[#7b3021]';
        failEl.textContent = 'Could not add to basket.';

        component.append(submitButton, successEl, failEl);

        component.addEventListener('success', () => {
            void reloadBasket(section);
        });

        card.appendChild(component);
        container.appendChild(card);
    }
};

const renderBasket = (basket, stockItems, section) => {
    const loading = section.querySelector('[data-basket-loading]');
    const emptyEl = section.querySelector('[data-basket-empty]');
    const contentEl = section.querySelector('[data-basket-content]');

    if (loading !== null) {
        loading.hidden = true;
    }

    const tickets = basketVal(basket, 'tickets') || [];
    const isEmpty = !Array.isArray(tickets) || tickets.length === 0;

    if (isEmpty) {
        if (emptyEl !== null) {
            emptyEl.hidden = false;
        }

        if (contentEl !== null) {
            contentEl.hidden = true;
        }

        return;
    }

    if (emptyEl !== null) {
        emptyEl.hidden = true;
    }

    if (contentEl !== null) {
        contentEl.hidden = false;
    }

    renderBasketSavings(basket, section);
    renderBasketMembershipUpsell(basket, section);
    renderBasketTickets(basket, section);
    renderBasketTotals(basket, section);
    renderBasketMerchandise(stockItems, section);
    void enrichMembershipUpsell(basket, section);
};

const reloadBasket = async (section) => {
    try {
        const response = await providerGet(section.dataset.basketUrl);

        if (!response.ok) {
            throw new Error('Basket reload failed.');
        }

        renderBasket(await response.json(), null, section);
    } catch {
        // Silently retain current render; user can reload the page.
    }
};

const hydrateBasket = async () => {
    const section = document.querySelector('[data-customer-basket]');

    if (section === null) {
        return;
    }

    try {
        const [basketResponse, stockResponse] = await Promise.all([
            providerGet(section.dataset.basketUrl),
            providerGet(section.dataset.stockItemsUrl),
        ]);

        if (!basketResponse.ok) {
            throw new Error('Basket unavailable.');
        }

        const basket = await basketResponse.json();
        const stockItems = stockResponse.ok ? await stockResponse.json() : [];

        renderBasket(basket, stockItems, section);
    } catch {
        const loading = section.querySelector('[data-basket-loading]');
        const errorEl = section.querySelector('[data-basket-error]');

        if (loading !== null) {
            loading.hidden = true;
        }

        if (errorEl !== null) {
            errorEl.hidden = false;
        }
    }
};

void hydrateBasket();

for (const form of document.querySelectorAll('[data-basket-promo-form]')) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const section = form.closest('[data-customer-basket]');
        const submitButton = form.querySelector('[data-basket-promo-submit]');
        const feedback = form.querySelector('[data-basket-promo-feedback]');
        const errorMessage = form.querySelector('[data-basket-promo-error]');
        const promoCode = form.elements.namedItem('promoCode')?.value?.trim() ?? '';

        if (feedback !== null) {
            feedback.hidden = true;
        }

        if (errorMessage !== null) {
            errorMessage.hidden = true;
        }

        submitButton.disabled = true;

        try {
            const response = await providerPatch(section.dataset.basketUrl, { promoCode });

            if (!response.ok) {
                throw new Error('Applying promo code failed.');
            }

            renderBasket(await response.json(), null, section);

            if (feedback !== null) {
                feedback.textContent = promoCode !== '' ? 'Code applied.' : 'Code removed.';
                feedback.hidden = false;
            }
        } catch {
            if (errorMessage !== null) {
                errorMessage.textContent = 'We could not apply that code. Please check it and try again.';
                errorMessage.hidden = false;
                errorMessage.focus();
            }
        } finally {
            submitButton.disabled = false;
        }
    });
}

// Checkout page

const initiateCheckout = async (section) => {
    const paymentsEl = document.getElementById('spektrixPayments');
    const paymentContainer = section.querySelector('[data-checkout-payment]');
    const loadingEl = section.querySelector('[data-checkout-loading]');
    const errorEl = section.querySelector('[data-checkout-error]');

    if (paymentsEl === null || paymentContainer === null) {
        return;
    }

    try {
        let paymentToken = null;

        const customerResponse = await providerGet(section.dataset.customerUrl);

        if (customerResponse.ok) {
            // Logged-in flow: resolve billing address, then initiate customer payment.
            // billing-address-id is required by <spektrix-payments> for customer checkout.
            // If no billing address can be resolved (CORS not yet configured, no addresses
            // saved, or any other failure), fall back to direct payment so the component
            // is always initialised correctly.
            let billingAddressId = null;

            try {
                const addressResponse = await providerGet(section.dataset.addressesUrl);

                if (addressResponse.ok) {
                    const addresses = await addressResponse.json();
                    const billing = Array.isArray(addresses)
                        ? addresses.find((a) => basketVal(a, 'isBilling') === true) || addresses[0]
                        : null;

                    if (billing !== null && billing !== undefined) {
                        billingAddressId = basketVal(billing, 'id');
                    }
                }
            } catch {
                // Address fetch blocked or failed — billingAddressId stays null.
            }

            if (billingAddressId !== null) {
                // Customer payment with billing address.
                const initiateResponse = await providerPost(section.dataset.initiateCustomerPaymentUrl);

                if (!initiateResponse.ok) {
                    throw new Error('Initiating customer payment failed.');
                }

                const data = await initiateResponse.json();
                paymentToken = basketVal(data, 'paymentToken') || basketVal(data, 'token');

                paymentsEl.setAttribute('billing-address-id', billingAddressId);
                paymentsEl.setAttribute('store-card', 'true');
            } else {
                // No billing address available — use direct payment flow.
                const initiateResponse = await providerPost(section.dataset.initiateDirectPaymentUrl);

                if (!initiateResponse.ok) {
                    throw new Error('Initiating direct payment failed.');
                }

                const data = await initiateResponse.json();
                paymentToken = basketVal(data, 'paymentToken') || basketVal(data, 'token');
            }
        } else {
            // Guest flow: direct payment initiation.
            const initiateResponse = await providerPost(section.dataset.initiateDirectPaymentUrl);

            if (!initiateResponse.ok) {
                throw new Error('Initiating direct payment failed.');
            }

            const data = await initiateResponse.json();
            paymentToken = basketVal(data, 'paymentToken') || basketVal(data, 'token');
        }

        if (paymentToken === null || paymentToken === '') {
            throw new Error('No payment token received.');
        }

        paymentsEl.setAttribute('payment-token', paymentToken);

        if (loadingEl !== null) {
            loadingEl.hidden = true;
        }

        paymentContainer.hidden = false;
    } catch {
        if (loadingEl !== null) {
            loadingEl.hidden = true;
        }

        if (errorEl !== null) {
            errorEl.hidden = false;
        }
    }
};

const hydrateCheckout = async () => {
    const section = document.querySelector('[data-customer-checkout]');

    if (section === null) {
        return;
    }

    const paymentsEl = document.getElementById('spektrixPayments');

    if (paymentsEl === null) {
        return;
    }

    const refusedEl = section.querySelector('[data-checkout-refused]');
    const expiredEl = section.querySelector('[data-checkout-expired]');
    const paymentContainer = section.querySelector('[data-checkout-payment]');
    const retryButton = section.querySelector('[data-checkout-retry]');
    const confirmationUrl = section.dataset.confirmationUrl || '/';

    paymentsEl.addEventListener('onPaymentCompleted', (event) => {
        const orderId = event?.detail?.orderId ?? '';
        const url = new URL(confirmationUrl, window.location.origin);

        if (orderId !== '') {
            url.searchParams.set('orderId', orderId);
        }

        window.location.assign(url.toString());
    });

    paymentsEl.addEventListener('onPaymentRefused', () => {
        if (paymentContainer !== null) {
            paymentContainer.hidden = true;
        }

        if (refusedEl !== null) {
            refusedEl.hidden = false;
        }
    });

    paymentsEl.addEventListener('onPaymentNotFound', () => {
        const basketUrl = section.dataset.basketUrlReturn || '/';
        const url = new URL(basketUrl, window.location.origin);
        url.searchParams.set('session_expired', '1');
        window.location.assign(url.toString());
    });

    paymentsEl.addEventListener('onError', () => {
        const loadingEl = section.querySelector('[data-checkout-loading]');
        const errorEl = section.querySelector('[data-checkout-error]');

        if (loadingEl !== null) {
            loadingEl.hidden = true;
        }

        if (paymentContainer !== null) {
            paymentContainer.hidden = true;
        }

        if (errorEl !== null) {
            errorEl.hidden = false;
        }
    });

    if (retryButton !== null) {
        retryButton.addEventListener('click', async () => {
            if (refusedEl !== null) {
                refusedEl.hidden = true;
            }

            const loadingEl = section.querySelector('[data-checkout-loading]');

            if (loadingEl !== null) {
                loadingEl.hidden = false;
            }

            await initiateCheckout(section);
        });
    }

    await initiateCheckout(section);
};

void hydrateCheckout();

// Checkout confirmation page

const hydrateCheckoutConfirmation = async () => {
    const section = document.querySelector('[data-checkout-confirmation]');

    if (section === null) {
        return;
    }

    const loadingEl = section.querySelector('[data-confirmation-loading]');
    const summaryEl = section.querySelector('[data-confirmation-summary]');
    const detailsEl = section.querySelector('[data-confirmation-order-details]');

    const orderId = new URLSearchParams(window.location.search).get('orderId');

    if (orderId === null || orderId === '') {
        if (loadingEl !== null) {
            loadingEl.hidden = true;
        }

        return;
    }

    try {
        const orderDetailUrl = section.dataset.ordersUrl
            .replace('/customer/orders', '/orders');
        const response = await providerGet(`${orderDetailUrl}/${encodeURIComponent(orderId)}`);

        if (!response.ok) {
            throw new Error('Loading order failed.');
        }

        const order = await response.json();

        if (loadingEl !== null) {
            loadingEl.hidden = true;
        }

        if (summaryEl !== null) {
            summaryEl.hidden = false;
        }

        if (detailsEl !== null) {
            renderOrderDetail(detailsEl, order);
        }
    } catch {
        if (loadingEl !== null) {
            loadingEl.hidden = true;
        }
    }
};

void hydrateCheckoutConfirmation();

for (const form of document.querySelectorAll('[data-customer-magic-link-authentication-form]')) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitButton = form.querySelector('[data-customer-magic-link-authentication-submit]');
        const errorMessage = form.querySelector('[data-customer-magic-link-authentication-error]');
        const token = new URLSearchParams(window.location.search).get('token');

        errorMessage.hidden = true;
        submitButton.disabled = true;

        try {
            if (token === null) {
                throw new Error('Magic link token missing.');
            }

            const response = await providerPost(form.dataset.authenticateMagicLinkUrl, { token });

            if (!response.ok) {
                throw new Error('Magic link authentication failed.');
            }

            storeCustomerSession(await response.json());
            window.location.assign(form.dataset.successUrl);
        } catch {
            errorMessage.hidden = false;
            errorMessage.focus();
            submitButton.disabled = false;
        }
    });
}
