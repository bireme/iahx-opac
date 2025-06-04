# CSRF Protection Implementation - Email Form

## Overview

This document provides comprehensive documentation for the Cross-Site Request Forgery (CSRF) protection implementation for the email form functionality in the IAHX OPAC application. The implementation uses Symfony's built-in security features to prevent malicious cross-site requests from exploiting the email sending functionality.

## Implementation Date

June 2, 2025

## Security Context

CSRF protection prevents malicious websites from exploiting the email form by forcing authenticated requests to include a cryptographically secure token that validates the request's authenticity. This ensures that only legitimate users interacting with the actual application interface can send emails through the system.

## Architecture Overview

The CSRF protection implementation follows Symfony's recommended patterns and consists of four main components:

1. **Form Type** (`src/Form/EmailFormType.php`) - Defines the form structure with CSRF protection
2. **Controller** (`src/Controller/SearchController.php`) - Handles form processing and validation
3. **Template** (`templates/regional/index.html`) - Renders the form with CSRF tokens
4. **Configuration** - Framework and security configuration files

## Implementation Details

### 1. Form Type Implementation

**File:** `src/Form/EmailFormType.php`

The `EmailFormType` class implements CSRF protection using Symfony's form component:

```php
public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'csrf_protection' => true,
        'csrf_field_name' => '_csrf_token',
        'csrf_token_id' => 'email_form',
    ]);
}
```

**Key Features:**

- **CSRF Protection Enabled:** `csrf_protection => true` activates token validation
- **Custom Field Name:** `_csrf_token` is used as the hidden field name in the form
- **Unique Token ID:** `email_form` identifier ensures tokens are specific to this form
- **Form Fields:** Includes all necessary email fields (from_name, from_email, to_email, subject, comment, selection)

### 2. Controller Integration

**File:** `src/Controller/SearchController.php`

The controller integrates CSRF protection through Symfony's form handling:

```php
use App\Form\EmailFormType;

// Form creation with CSRF protection
$email_form = $this->createForm(EmailFormType::class);
$email_form->handleRequest($request);

// CSRF validation through form validation
if ($email_form->isSubmitted() && $email_form->isValid()) {
    // Process email sending only after CSRF validation passes
    // ... email processing logic
} else {
    // Handle validation failures (including CSRF failures)
    $this->addFlash('error', 'There was a problem with your request. Please try again.');
}
```

**Security Features:**

- **Automatic Validation:** `isValid()` method includes CSRF token validation
- **Error Handling:** Flash messages notify users of validation failures
- **Request Protection:** Email processing only occurs after successful CSRF validation

### 3. Template Implementation

**File:** `templates/regional/index.html`

The template uses Symfony form helpers to automatically include CSRF tokens:

```twig
{{ form_start(email_form, {'action': path('Search'), 'method': 'POST'}) }}
    {{ form_errors(email_form) }}
    {{ form_widget(email_form.from_name) }}
    {{ form_widget(email_form.from_email) }}
    {{ form_widget(email_form.to_email) }}
    {{ form_widget(email_form.subject) }}
    {{ form_widget(email_form.comment) }}
    {{ form_widget(email_form.selection) }}
{{ form_end(email_form) }}
```

**Security Benefits:**

- **Automatic Token Inclusion:** `form_start()` automatically includes the CSRF token as a hidden field
- **Error Display:** `form_errors()` shows CSRF validation errors to users
- **Form Integrity:** All form elements are rendered through Symfony helpers

### 4. Configuration

**Framework Configuration:** `config/packages/framework.yaml`

```yaml
framework:
  csrf_protection: true
```

**Security Configuration:** `config/packages/security.yaml`

```yaml
security:
  # CSRF protection is enabled framework-wide
```

## Security Properties

### Token Generation

- **Algorithm:** URI-safe tokens using Symfony's `UriSafeTokenGenerator`
- **Storage:** Session-based storage via `SessionTokenStorage`
- **Uniqueness:** Each form instance receives a unique token
- **Expiration:** Tokens expire with the user session

### Validation Process

1. Token generation occurs when form is rendered
2. Token is included as hidden field in form submission
3. Server validates token against stored session value
4. Form processing only continues if validation succeeds
5. New token is generated for subsequent form displays

### Attack Prevention

- **Cross-Site Request Forgery:** Prevents unauthorized email sending from external sites
- **Session Hijacking Mitigation:** Tokens are tied to specific sessions
- **Replay Attack Prevention:** Tokens are single-use and expire appropriately

## Testing and Validation

### Manual Testing Scenarios

1. **Valid Form Submission:**

   - Fill out email form normally
   - Submit form
   - Verify email is sent successfully
   - Confirm new CSRF token is generated

2. **Invalid CSRF Token:**

   - Modify CSRF token value in browser developer tools
   - Submit form
   - Verify submission is rejected with error message

3. **Missing CSRF Token:**

   - Remove CSRF token field from form
   - Submit form
   - Verify submission fails with validation error

4. **Cross-Site Request Test:**
   - Create external form targeting the email endpoint
   - Submit from different domain
   - Verify request is rejected due to missing/invalid token

### Browser Testing

- **Modern Browsers:** Chrome, Firefox, Safari, Edge
- **JavaScript Disabled:** Form should work without JavaScript
- **Session Scenarios:** Test with fresh sessions and existing sessions

## Security Considerations

### Token Lifecycle

- Tokens are regenerated after each form submission
- Expired sessions invalidate all associated tokens
- Token storage is handled securely by Symfony's session system

### Error Handling

- CSRF failures display generic error messages to prevent information disclosure
- Failed attempts do not reveal specific validation failure reasons
- Users are prompted to refresh and try again

### Performance Impact

- Minimal overhead from token generation and validation
- Session storage adds negligible memory usage
- No significant impact on form rendering or submission times

## Maintenance Guidelines

### Regular Security Reviews

- Review CSRF implementation during security audits
- Monitor for framework security updates affecting CSRF components
- Validate token generation remains cryptographically secure

### Code Maintenance

- Keep Symfony security components updated
- Review form type configurations during code reviews
- Ensure any new email-related forms implement similar CSRF protection

### Monitoring

- No specific logging is implemented for CSRF failures
- Monitor application logs for unusual form submission patterns
- Watch for user reports of form submission failures

## Troubleshooting

### Common Issues

1. **Form Submission Failures:**

   - Check session configuration
   - Verify CSRF protection is enabled in framework config
   - Ensure form template uses Symfony form helpers

2. **Token Validation Errors:**

   - Clear browser cache and cookies
   - Check for JavaScript errors affecting form submission
   - Verify session storage is functioning correctly

3. **Configuration Problems:**
   - Confirm `symfony/security-csrf` component is installed
   - Validate framework.yaml CSRF settings
   - Check form type CSRF configuration

### Debug Steps

1. Verify form type extends `AbstractType` correctly
2. Check controller uses `createForm()` method properly
3. Ensure template uses `form_start()` and `form_end()` helpers
4. Validate session storage is working via other session features

## Related Documentation

- [Symfony CSRF Protection Documentation](https://symfony.com/doc/current/security/csrf.html)
- [Symfony Form Component Documentation](https://symfony.com/doc/current/forms.html)
- [OWASP CSRF Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)

## Change History

| Date         | Version | Changes                                    | Author           |
| ------------ | ------- | ------------------------------------------ | ---------------- |
| June 2, 2025 | 1.0     | Initial CSRF implementation for email form | Development Team |

---

**Note:** This implementation satisfies security audit requirements for CSRF protection on the email functionality. The solution uses Symfony's proven security components and follows framework best practices for maximum security and maintainability.
