import { test, expect } from '@playwright/test';
import { randomInt } from 'node:crypto';
import { faker } from '@faker-js/faker';

test('User data must be sent to the boarding service when using UTM and referral fbpixel', async ({ page }) => {
  	await page.goto('http://client-rrfx.test/signup?referral=fbpixel&utm_source=google&utm_medium=cpc&utm_campaign=test_campaign&utm_term=test_term&utm_content=test_content');
	await page.fill('input[name="fullname"]', `${faker.person.firstName()} ${faker.person.lastName()}`);
	await page.fill('input[name="email"]', faker.internet.email());
	await page.fill('input[name="password"]', "Test@123");
	await page.fill('input[name="phone"]', faker.phone.number({style: 'national'}));
	await page.check('input[name="terms"]');

	const [request, response] = await Promise.all([
		page.waitForRequest(req => req.url().includes('/ajax/auth/signup') && req.method() === 'POST'),
		page.waitForResponse(r => r.url().includes('/ajax/auth/signup')),
		page.click('button[type="submit"]')
	])
	
	const payload = await request.postData();
	const json = await response.json();

	expect(json).toMatchObject({
		success: true,
		message: "user data send to boarding process",
	})
});

test('User data must be sent to boarding service when using UTM without a referral', async ({ page }) => {
  	await page.goto('http://client-rrfx.test/signup?utm_source=google&utm_medium=cpc&utm_campaign=test_campaign&utm_term=test_term&utm_content=test_content');
	await page.fill('input[name="fullname"]', `${faker.person.firstName()} ${faker.person.lastName()}`);
	await page.fill('input[name="email"]', faker.internet.email());
	await page.fill('input[name="password"]', "Test@123");
	await page.fill('input[name="phone"]', faker.phone.number({style: 'national'}));
	await page.check('input[name="terms"]');

	const [request, response] = await Promise.all([
		page.waitForRequest(req => req.url().includes('/ajax/auth/signup') && req.method() === 'POST'),
		page.waitForResponse(r => r.url().includes('/ajax/auth/signup')),
		page.click('button[type="submit"]')
	])
	
	const payload = await request.postData();
	const json = await response.json();

	expect(json).toMatchObject({
		success: true,
		message: "user data send to boarding process",
	})
});

test('User data must be sent to boarding service when using referral fbpixel without UTM', async ({ page }) => {
  	await page.goto('http://client-rrfx.test/signup?referral=fbpixel');
	await page.fill('input[name="fullname"]', `${faker.person.firstName()} ${faker.person.lastName()}`);
	await page.fill('input[name="email"]', faker.internet.email());
	await page.fill('input[name="password"]', "Test@123");
	await page.fill('input[name="phone"]', faker.phone.number({style: 'national'}));
	await page.check('input[name="terms"]');

	const [request, response] = await Promise.all([
		page.waitForRequest(req => req.url().includes('/ajax/auth/signup') && req.method() === 'POST'),
		page.waitForResponse(r => r.url().includes('/ajax/auth/signup')),
		page.click('button[type="submit"]')
	])
	
	const payload = await request.postData();
	const json = await response.json();

	expect(payload).toContain('referral=fbpixel');
	expect(json).toMatchObject({
		success: true,
		message: "user data send to boarding process",
	})
});

test('User data must be sent to the boarding service when without UTM and referral', async ({ page }) => {
  	await page.goto('http://client-rrfx.test/signup');
	await page.fill('input[name="fullname"]', `${faker.person.firstName()} ${faker.person.lastName()}`);
	await page.fill('input[name="email"]', faker.internet.email());
	await page.fill('input[name="password"]', "Test@123");
	await page.fill('input[name="phone"]', faker.phone.number({style: 'national'}));
	await page.check('input[name="terms"]');

	const [request, response] = await Promise.all([
		page.waitForRequest(req => req.url().includes('/ajax/auth/signup') && req.method() === 'POST'),
		page.waitForResponse(r => r.url().includes('/ajax/auth/signup')),
		page.click('button[type="submit"]')
	])
	
	const payload = await request.postData();
	const json = await response.json();

	expect(json).toMatchObject({
		success: true,
		message: "user data send to boarding process",
	})
});

test('User data should not sent to boarding services when using referrals other than fbpixel', async ({ page }) => {
  	await page.goto('http://client-rrfx.test/signup?referral=admin');
	await page.fill('input[name="fullname"]', `${faker.person.firstName()} ${faker.person.lastName()}`);
	await page.fill('input[name="email"]', faker.internet.email());
	await page.fill('input[name="password"]', "Test@123");
	await page.fill('input[name="phone"]', faker.phone.number({style: 'national'}));
	await page.check('input[name="terms"]');

	const [request, response] = await Promise.all([
		page.waitForRequest(req => req.url().includes('/ajax/auth/signup') && req.method() === 'POST'),
		page.waitForResponse(r => r.url().includes('/ajax/auth/signup')),
		page.click('button[type="submit"]')
	])
	
	const payload = await request.postData();
	const json = await response.json();

	expect(payload).not.toContain('referral=fbpixel');
	expect(json).toMatchObject({
		success: true,
		message: "Registrasi berhasil",
	})
});