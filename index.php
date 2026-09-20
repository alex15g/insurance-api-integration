<?php
session_start();
// Daca utilizatorul nu este logat, ii dam un "șut" inapoi la pagina de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calculator RCA</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div style="background-color: #fff; padding: 10px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: right; margin-bottom: 20px;">
    <span style="margin-right: 15px; color: #555;">Logat ca: <strong><?php echo $_SESSION['email']; ?></strong></span>
    <a href="profil.php" style="text-decoration: none; background: #3b82f6; color: white; padding: 5px 15px; border-radius: 5px; font-weight: bold; margin-right: 10px;">Profilul Meu</a>
    <a href="logout.php" style="text-decoration: none; color: #dc2626; font-weight: bold;">Deconectare</a>
</div>

<!-- Bara de sus cu user-ul logat si butonul de Logout -->
<div style="display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <div>
        Salutare, <b><?php echo $_SESSION['email']; ?></b> 👋
    </div>
    <a href="logout.php" style="background-color: #ef4444; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-weight: bold; transition: 0.2s;">Logout (Ieșire)</a>
</div>

<div class="container">
  <h1 class="page-title">Calculator RCA — ofertă nouă</h1>

    <!-- Date Vehicul -->
    <div class="card">
        <h2>Date Vehicul</h2>
        <div class="form-grid">

            <!-- AICI AM MODIFICAT: AM ADAUGAT BUTONUL LÂNGA INPUT -->
            <div class="input-group">
                <label for="licensePlate">Număr înmatriculare *</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="licensePlate" placeholder="ex: CJ01ABC" required style="flex: 1;">
                    <button type="button" id="btn-cauta-auto" style="background-color: #f59e0b; color: white; padding: 0 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">🔍 Caută</button>
                </div>
                <span id="mesaj-auto" style="font-size: 0.85em; font-weight: bold; margin-top: 5px; display: block;"></span>
            </div>
            <!-- ================================================== -->

            <div class="input-group">
                <label for="vin">Serie șasiu (VIN) *</label>
                <input type="text" id="vin" placeholder="17 caractere" maxlength="17" required>
            </div>
      <div class="input-group">
        <label for="vehicleType">Tip Vehicul *</label>
        <select id="vehicleType" required>
          <option value="M1">Autoturism (M1)</option>
          <option value="N1">Autoutilitară (N1)</option>
          <option value="L3e">Motocicletă (L3e)</option>
        </select>
      </div>
      <div class="input-group">
        <label for="fuelType">Combustibil *</label>
        <select id="fuelType" required>
          <option value="diesel">Diesel</option>
          <option value="petrol">Benzină</option>
          <option value="electric">Electric</option>
          <option value="hybrid">Hibrid</option>
        </select>
      </div>
      <div class="input-group">
        <label for="engineDisplacement">Capacitate cilindrică (cm3)</label>
        <input type="number" id="engineDisplacement" placeholder="ex: 1968" required>
      </div>
      <div class="input-group">
        <label for="enginePower">Putere (KW)</label>
        <input type="number" id="enginePower" placeholder="ex: 110" required>
      </div>
      <div class="input-group">
        <label for="currentMileage">Kilometraj (la bord)</label>
        <input type="number" id="currentMileage" placeholder="ex: 150000">
        <span class="hint">Obligatoriu pentru Grawe</span>
      </div>
        <div class="input-group">
            <label for="brand">Marcă vehicul *</label>
            <input type="text" id="brand" placeholder="ex: BMW" required>
        </div>
        <div class="input-group">
            <label for="model">Model *</label>
            <input type="text" id="model" placeholder="ex: Seria 3" required>
        </div>
        <div class="input-group">
            <label for="yearOfConstruction">An fabricație *</label>
            <input type="number" id="yearOfConstruction" placeholder="ex: 2018" required>
        </div>
        <div class="input-group">
            <label for="totalWeight">Masă maximă autorizată (kg) *</label>
            <input type="number" id="totalWeight" placeholder="ex: 2000" required>
        </div>
        <div class="input-group">
            <label for="seats">Număr locuri *</label>
            <input type="number" id="seats" placeholder="ex: 5" required>
        </div>
    </div>
  </div>

  <!-- Date Proprietar -->
  <div class="card">
    <h2>Date Proprietar (Persoană Fizică)</h2>
    <div class="form-grid">
      <div class="input-group">
        <label for="lastName">Nume *</label>
        <input type="text" id="lastName" required>
      </div>
      <div class="input-group">
        <label for="firstName">Prenume *</label>
        <input type="text" id="firstName" required>
      </div>
      <div class="input-group">
        <label for="taxId">CNP *</label>
        <input type="text" id="taxId" maxlength="13" required>
      </div>
      <div class="input-group">
        <label for="idNumber">Serie și Număr CI *</label>
        <input type="text" id="idNumber" placeholder="ex: KX123456" required>
      </div>
      <div class="input-group">
        <label for="drivingLicenseDate">Dată obținere permis auto</label>
        <input type="date" id="drivingLicenseDate">
        <span class="hint">Obligatoriu pentru Grawe</span>
      </div>
      <div class="input-group">
        <label for="email">Email</label>
        <input type="email" id="email" placeholder="client@email.com">
        <span class="hint">Obligatoriu pentru Eazy Insure</span>
      </div>
      <div class="input-group">
        <label for="phone">Telefon</label>
        <input type="tel" id="phone" placeholder="07...">
      </div>
    </div>
  </div>

  <!-- Adresa -->
  <div class="card">
    <h2>Adresă</h2>
    <div class="form-grid">
        <div class="input-group">
            <label for="county">Județ *</label>
            <select id="county" required>
                <option value="">Alege...</option>
                <option value="CJ">CLUJ</option> <!-- Atributul value TREBUIE să fie "CJ" -->
                <option value="AB">ALBA</option>
                <option value="B">BUCURESTI</option> <!-- Atributul value TREBUIE să fie "B" -->
            </select>
        </div>
      <div class="input-group">
        <label for="city">Localitate *</label>
        <input type="text" id="city" placeholder="ex: CLUJ-NAPOCA" required>
      </div>
      <div class="input-group">
        <label for="street">Stradă *</label>
        <input type="text" id="street" placeholder="ex: Principala" required>
      </div>
      <div class="input-group">
        <label for="houseNumber">Număr</label>
        <input type="text" id="houseNumber">
        <span class="hint">Obligatoriu pentru Axeria</span>
      </div>
      <div class="input-group">
        <label for="building">Bloc</label>
        <input type="text" id="building">
      </div>
      <div class="input-group">
        <label for="staircase">Scară</label>
        <input type="text" id="staircase">
      </div>
      <div class="input-group">
        <label for="floor">Etaj</label>
        <input type="text" id="floor">
        <span class="hint">Obligatoriu pentru Grawe</span>
      </div>
      <div class="input-group">
        <label for="apartment">Apartament</label>
        <input type="text" id="apartment">
      </div>
      <div class="input-group">
        <label for="postcode">Cod poștal</label>
        <input type="text" id="postcode">
        <span class="hint">Obligatoriu pentru Generali și Grawe</span>
      </div>
        <div class="input-group">
            <label for="cityCode">Cod SIRUTA Localitate *</label>
            <input type="number" id="cityCode" placeholder="ex: 54985" required>
            <span class="hint">Codul SIRUTA al orașului (ex: 54985 pentru Cluj)</span>
        </div>
    </div>
  </div>

  <!-- Polita -->
  <div class="card">
    <h2>Poliță</h2>
    <div class="form-grid">
      <div class="input-group">
        <label for="startDate">Data de început *</label>
        <input type="date" id="startDate" required>
      </div>
      <div class="input-group">
        <label for="termTime">Durata (luni) *</label>
        <select id="termTime" required>
          <option value="12">12</option>
          <option value="6">6</option>
          <option value="1">1</option>
        </select>
      </div>
      <div class="input-group">
        <label for="installmentCount">Număr de rate</label>
        <select id="installmentCount">
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="4">4</option>
        </select>
        <span class="hint">Nu toți asigurătorii acceptă rate</span>
      </div>
      <div class="input-group">
        <label for="commission">Comision broker (%)</label>
        <input type="number" id="commission" value="10" min="0" max="100">
      </div>
      <div class="input-group">
        <label for="bmPrev">Clasă bonus-malus anterioară</label>
        <select id="bmPrev">
          <option value="B0">B0</option>
          <option value="B1">B1</option>
          <option value="B8">B8</option>
        </select>
      </div>
      <div class="input-group">
        <label for="bmCurrent">Clasă bonus-malus curentă</label>
        <select id="bmCurrent">
          <option value="B0">B0</option>
          <option value="B1">B1</option>
          <option value="B8">B8</option>
        </select>
        <span class="hint">Obligatoriu pentru Omniasig</span>
      </div>
      <div class="input-group">
        <label for="claimsCount">Daune plătite anul anterior</label>
        <input type="number" id="claimsCount" value="0" min="0">
      </div>
    </div>
  </div>
    <div class="input-group">
        <label for="insurerName">Asigurător *</label>
        <select id="insurerName" required>
            <option value="ALL">Toți asigurătorii (Comparare prețuri)</option>
            <option value="allianz">Allianz</option>
            <option value="groupama">Groupama</option>
            <option value="omniasig">Omniasig</option>
            <option value="axeria">Axeria</option>
        </select>
    </div>
  <!--Declaratii -->
  <div class="card">
    <h2>Declarații specifice asigurătorilor</h2>
    <div class="form-grid">
      <div class="input-group">
        <label for="itpDate">Expirare ITP</label>
        <input type="date" id="itpDate">
        <span class="hint">Fără această dată, Generali nu poate oferta</span>
      </div>
      <div class="input-group">
        <label for="bodyType">Tip caroserie</label>
        <select id="bodyType">
          <option value="HATCHBACK">HATCHBACK</option>
          <option value="SEDAN">SEDAN</option>
          <option value="SUV">SUV</option>
        </select>
        <span class="hint">Folosit de Hellas Autonom și NextIns</span>
      </div>
      <div class="input-group">
        <label for="transferDate">Data transferului de proprietate</label>
        <input type="date" id="transferDate">
        <span class="hint">Necesar la Hellas dacă vehiculul nu este nou</span>
      </div>
    </div>

    <div class="checkbox-row">
      <label class="checkbox-label">
        <input type="checkbox" id="tempRegistration"> Transfer de proprietate (număr temporar)
      </label>
      <label class="checkbox-label">
        <input type="checkbox" id="over35"> Toți conducătorii au peste 35 de ani
      </label>
      <label class="checkbox-label">
        <input type="checkbox" id="pep"> Persoană expusă public
      </label>
    </div>
    <span class="hint block-hint">Declarațiile de mai sus se dau pe proprie răspundere și influențează prima calculată.</span>
  </div>

  <!-- Buton Submit -->
  <button id="btnSubmit" class="btn-primary">Se interoghează asigurătorii...</button>
  <div class="loading-text">Interogarea tuturor asigurătorilor poate dura până la un minut.</div>
</div>
<!-- sectiunea cu oferte care sta ascunsa pana dam submit -->
<div id="resultsSection" class="card" style="display: none; margin-top: 20px;">
  <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
    <h2 style="border: none; margin: 0; padding: 0;">Oferte RCA Generate</h2>
    <button id="btnBack" class="btn-secondary">Inapoi la formular</button>
  </div>

  <div style="overflow-x: auto;">
    <table class="quotes-table">
      <thead>
      <tr>
        <th>Status</th>
        <th>Asigurator</th>
        <th>Pret calculat</th>
        <th>Actiune</th>
      </tr>
      </thead>
      <tbody id="quotesTableBody">
      <!-- JS-ul o sa bage aici randurile automat -->
      </tbody>
    </table>
  </div>
</div>

<script src="app.js?v=2"></script>
</body>
</html>