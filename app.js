// === SISTEM AUTO-COMPLETARE DATE AUTO ===
document.getElementById('btn-cauta-auto').addEventListener('click', function() {
    let nrAuto = document.getElementById('licensePlate').value.trim();
    let mesajDiv = document.getElementById('mesaj-auto');

    if(nrAuto === '') {
        mesajDiv.style.color = 'red';
        mesajDiv.innerText = 'Scrie un număr de înmatriculare!';
        return;
    }

    mesajDiv.style.color = 'blue';
    mesajDiv.innerText = 'Se caută mașina...';

    // Apelam fisierul backend pe care l-ai creat anterior
    fetch('api_vehicul.php?nr_auto=' + encodeURIComponent(nrAuto))
        .then(response => response.json())
        .then(data => {
            if (data.error === false) {
                mesajDiv.style.color = 'green';
                mesajDiv.innerText = 'Datele au fost completate automat!';

                let m = data.date_masina;

                // Populam campurile fix cu ID-urile tale
                if(document.getElementById('vin')) document.getElementById('vin').value = m.vin || '';
                if(document.getElementById('brand')) document.getElementById('brand').value = m.make || '';
                if(document.getElementById('model')) document.getElementById('model').value = m.model || '';
                if(document.getElementById('yearOfConstruction')) document.getElementById('yearOfConstruction').value = m.yearOfConstruction || '';
                if(document.getElementById('engineDisplacement')) document.getElementById('engineDisplacement').value = m.engineDisplacement || '';
                if(document.getElementById('enginePower')) document.getElementById('enginePower').value = m.enginePower || '';
                if(document.getElementById('totalWeight')) document.getElementById('totalWeight').value = m.totalWeight || '';
                if(document.getElementById('seats')) document.getElementById('seats').value = m.seats || '';

            } else {
                mesajDiv.style.color = 'red';
                mesajDiv.innerText = data.message;
            }
        })
        .catch(err => {
            mesajDiv.style.color = 'red';
            mesajDiv.innerText = 'Eroare de server.';
        });
});

document.addEventListener("DOMContentLoaded", () => {

    // 1. LOGICA PENTRU CHECKBOX TRANSFER PROPRIETATE
    const tempRegistrationCheck = document.getElementById('tempRegistration');
    const transferDateInput = document.getElementById('transferDate');
    const licensePlateInput = document.getElementById('licensePlate');

    tempRegistrationCheck.addEventListener('change', (event) => {
        if (event.target.checked) {
            transferDateInput.setAttribute('required', 'true');
            transferDateInput.style.borderColor = '#0056b3';
            licensePlateInput.removeAttribute('required');
        } else {
            transferDateInput.removeAttribute('required');
            transferDateInput.style.borderColor = '#ccc';
            transferDateInput.value = '';
            licensePlateInput.setAttribute('required', 'true');
        }
    });

    // 2. LOGICA SUBMIT (BUTONUL DE CALCUL)
    const btnSubmit = document.getElementById('btnSubmit');

    btnSubmit.addEventListener('click', (event) => {
        event.preventDefault(); // Oprim reîncărcarea paginii

        // A. Colectarea datelor (Vehicul)
        const licensePlate = document.getElementById('licensePlate').value.trim();
        const vin = document.getElementById('vin').value.trim();
        const vehicleType = document.getElementById('vehicleType').value;
        const fuelType = document.getElementById('fuelType').value;
        const engineDisplacement = parseInt(document.getElementById('engineDisplacement').value) || 0;
        const enginePower = parseInt(document.getElementById('enginePower').value) || 0;
        const currentMileage = parseInt(document.getElementById('currentMileage').value) || undefined;
        const brand = document.getElementById('brand').value.trim();
        const model = document.getElementById('model').value.trim();
        const yearOfConstruction = parseInt(document.getElementById('yearOfConstruction').value) || 0;
        const totalWeight = parseInt(document.getElementById('totalWeight').value) || 0;
        const seats = parseInt(document.getElementById('seats').value) || 0;

        // B. Colectarea datelor (Proprietar)
        const lastName = document.getElementById('lastName').value.trim();
        const firstName = document.getElementById('firstName').value.trim();
        const taxId = document.getElementById('taxId').value.trim();
        const idNumber = document.getElementById('idNumber').value.trim();
        const drivingLicenseDate = document.getElementById('drivingLicenseDate').value;
        const email = document.getElementById('email').value.trim() || undefined;
        const phone = document.getElementById('phone').value.trim() || undefined;

        // C. Colectarea datelor (Adresa)
        const county = document.getElementById('county').value;
        const city = document.getElementById('city').value.trim();
        const street = document.getElementById('street').value.trim();
        const houseNumber = document.getElementById('houseNumber').value.trim();
        const postcode = document.getElementById('postcode').value.trim();
        const cityCode = parseInt(document.getElementById('cityCode').value) || 0;

        // D. Colectarea datelor (Polita)
        const startDate = document.getElementById('startDate').value;
        const termTime = parseInt(document.getElementById('termTime').value);
        const installmentCount = parseInt(document.getElementById('installmentCount').value);
        const commission = parseFloat(document.getElementById('commission').value);
        const bmCurrent = document.getElementById('bmCurrent').value;

        // E. Colectarea datelor (Declaratii)
        const itpDate = document.getElementById('itpDate').value;
        const bodyType = document.getElementById('bodyType').value;
        const isTransfer = tempRegistrationCheck.checked;
        const over35 = document.getElementById('over35').checked;
        const isPep = document.getElementById('pep').checked;

        // F. VALIDARI STRICTE FRONTEND
        let errors = [];
        if (!lastName || !firstName || !taxId) errors.push("Numele, Prenumele și CNP-ul sunt obligatorii.");
        if (taxId && taxId.length !== 13) errors.push("CNP-ul trebuie să aibă exact 13 caractere.");
        if (!vin || vin.length !== 17) errors.push("Seria de șasiu (VIN) trebuie să aibă exact 17 caractere.");
        if (!isTransfer && !licensePlate) errors.push("Numărul de înmatriculare este obligatoriu pentru vehiculele înmatriculate.");
        if (!county || !city || !street) errors.push("Județul, Localitatea și Strada sunt obligatorii.");
        if (!startDate) errors.push("Te rugăm să selectezi Data de început a poliței.");
        if (isTransfer && termTime !== 1) errors.push("Pentru numere temporare, durata poliței poate fi doar de 1 lună.");

        if (errors.length > 0) {
            alert("Atenție:\n- " + errors.join("\n- "));
            return;
        }

        btnSubmit.innerText = "Se încarcă oferte...";
        btnSubmit.disabled = true;

        const selectedInsurer = document.getElementById('insurerName').value;
        const asiguratoriDeInterogat = selectedInsurer === "ALL"
            ? ["allianz", "omniasig", "axeria", "groupama"]
            : [selectedInsurer];

        // G. CONSTRUIREA PAYLOAD-ULUI DE BAZĂ
        const basePayload = {
            provider: { organization: { businessName: "" } },
            product: {
                motor: {
                    startDate: startDate,
                    termTime: termTime,
                    installmentCount: installmentCount,
                    commissionPercentLimit: commission
                },
                policyholder: {
                    lastName: lastName,
                    firstName: firstName,
                    taxId: taxId,
                    email: email,
                    mobileNumber: phone,
                    politicallyExposed: isPep ? "yes" : "no",
                    identification: {
                        idType: "CI",
                        idNumber: idNumber
                    },
                    drivingLicense: drivingLicenseDate ? { issueDate: drivingLicenseDate } : undefined,
                    address: {
                        country: "RO", county: county, city: city, cityCode: cityCode,
                        street: street, houseNumber: houseNumber || undefined, postcode: postcode || undefined
                    }
                },
                vehicle: {
                    registrationType: isTransfer ? "temporaryRegistered" : "registered",
                    licensePlate: isTransfer ? undefined : licensePlate,
                    vin: vin, brand: brand, model: model, yearOfConstruction: yearOfConstruction,
                    totalWeight: totalWeight, seats: seats, vehicleType: vehicleType,
                    fuelType: fuelType, engineDisplacement: engineDisplacement, enginePower: enginePower,
                    currentMileage: currentMileage, bodyType: bodyType, usageType: "personal",
                    expirationDatePti: itpDate || undefined, isOwnerTransfer: isTransfer,
                    identification: { idNumber: "X123456" }
                }
            }
        };

        // Pregătim UI-ul
        const tableBody = document.querySelector('.quotes-table tbody');
        tableBody.innerHTML = '';
        const cards = document.querySelectorAll('.card:not(#resultsSection)');
        cards.forEach(card => card.style.display = 'none');
        btnSubmit.style.display = 'none';
        document.getElementById('resultsSection').style.display = 'block';

        // H. APELURI MULTIPLE ÎN PARALEL
        asiguratoriDeInterogat.forEach(ins => {
            let currentPayload = JSON.parse(JSON.stringify(basePayload));
            currentPayload.provider.organization.businessName = ins;

            fetch('api_oferta.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(currentPayload)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error === true) {
                        tableBody.innerHTML += `
                        <tr style="background-color: #ffeaea;">
                            <td><strong>${ins.toUpperCase()}</strong></td>
                            <td colspan="2" style="color: red; font-size: 0.9rem;">${data.message || "Eroare validare"}</td>
                            <td>-</td>
                        </tr>
                    `;
                    } else if (data.data && data.data.offers) {
                        let numeAsigurator = data.data.provider.organization.businessName.toUpperCase();
                        data.data.offers.forEach(oferta => {
                            tableBody.innerHTML += `
                            <tr>
                                <td><strong>${numeAsigurator}</strong></td>
                                <td>Clasa ${oferta.bonusMalusClass}</td>
                                <td><span style="color: #28a745; font-weight: bold;">${oferta.premiumAmount} ${oferta.currency}</span></td>
                                <td>
                                    <button class="btn-issue" style="background-color:#28a745; color:white; border:none; padding:8px 12px; cursor:pointer; border-radius:5px;" 
                                            onclick="emitePolita(${oferta.offerId}, ${oferta.premiumAmount})">
                                        Cumpără Acum
                                    </button>
                                </td>
                            </tr>
                        `;
                        });
                    }
                })
                .catch(err => console.error(`Eroare la ${ins}:`, err));
        });

        btnSubmit.innerText = "Calculează RCA";
        btnSubmit.disabled = false;

        const loadingText = document.getElementById('loadingText');
        if(loadingText) loadingText.style.display = 'none';
    });

    // 3. LOGICA PENTRU BUTONUL "ÎNAPOI LA FORMULAR"
    document.getElementById('btnBack').addEventListener('click', () => {
        document.getElementById('resultsSection').style.display = 'none';
        const cards = document.querySelectorAll('.card:not(#resultsSection)');
        cards.forEach(card => card.style.display = 'block');
        btnSubmit.style.display = 'block';
    });

}); // <-- Aici se închide DOMContentLoaded corect

// 4. FUNCȚIA DE EMITERE POLIȚĂ (GLOBALĂ - în afara DOMContentLoaded)
// Funcția care se apelează când utilizatorul dă click pe "Cumpără Acum"
// 4. FUNCȚIA DE EMITERE POLIȚĂ (GLOBALĂ - în afara DOMContentLoaded)
function emitePolita(offerId, pret) {
    const confirma = confirm(`Ești sigur că vrei să emiți polița la prețul de ${pret} RON?`);
    if (!confirma) return;

    // Schimbăm cursorul în clepsidră ca să vadă că se încarcă
    document.body.style.cursor = 'wait';

    const cerereEmitere = {
        offerId: offerId,
        payment: {
            method: "receipt",
            currency: "RON",
            amount: pret,
            date: new Date().toISOString().split('T')[0],
            documentNumber: "CHITANTA-" + Math.floor(Math.random() * 10000)
        }
    };

    fetch('api_emite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(cerereEmitere)
    })
        .then(res => res.json())
        .then(data => {
            document.body.style.cursor = 'default'; // Punem cursorul la loc

            if (data.error === true || data.status !== 200) {
                alert("Eroare la emitere: " + (data.message || "A apărut o problemă."));
            } else if (data.data && data.data.policies && data.data.policies.length > 0) {
                const policyId = data.data.policies[0].policyId;

                // AICI E MAGIA PENTRU EMAIL!
                // Înlocuim tabelul de oferte cu un mesaj frumos de succes + formularul de email
                const htmlSucces = `
                    <div style="padding: 30px; background-color: #d1fae5; border-radius: 8px; text-align: center; border: 2px solid #34d399;">
                        <h2 style="color: #065f46; margin-top: 0;">✅ Polița a fost emisă cu succes!</h2>
                        <p style="margin-bottom: 20px;">ID Poliță: <strong>${policyId}</strong></p>
                        
                        <a href="descarca_pdf.php?policyId=${policyId}" target="_blank" style="background: #3b82f6; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                            📥 Descarcă PDF în Calculator
                        </a>
                        
                        <hr style="border: 0; border-top: 1px solid #a7f3d0; margin: 30px 0;">
                        
                        <h3 style="color: #065f46; margin-bottom: 15px;">Trimite polița clientului:</h3>
                        
                        <form action="trimite_polita_email.php" method="GET" target="_blank" style="display: flex; justify-content: center; gap: 10px; align-items: center;">
                            <input type="hidden" name="policyId" value="${policyId}">
                            
                            <input type="email" name="email_client" placeholder="Ex: client@gmail.com" required style="padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;">
                            
                            <button type="submit" style="background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px;">
                                ✉️ Trimite PDF pe Email
                            </button>
                        </form>
                    </div>
                `;

                // Punem HTML-ul creat mai sus în locul tabelului cu oferte
                document.getElementById('resultsSection').innerHTML = htmlSucces;
            }
        })
        .catch(err => {
            document.body.style.cursor = 'default';
            console.error(err);
            alert('Eroare de conexiune la serverul tău.');
        });
}