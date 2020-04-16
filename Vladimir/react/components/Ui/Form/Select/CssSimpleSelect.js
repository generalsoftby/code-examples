export function cssSimpleSelect(height = 44) {
    return {
        container: (base, state) => ({
            ...base,
            width: "100%",
            border: "1px solid #7EC7ED",
            borderRadius: "4px",
            background: '#FFFFFF',
        }),
        control: (base, state) => ({
            ...base,
            boxShadow: "none",
            height: height,
            minHeight: 0,
            borderRadius: 4,
            border: 0,
            background: '#FFFFFF',
            cursor: "pointer",
            padding: 0,
        }),
        input: (base, state) => ({
            ...base,
            padding: 0,
            fontFamily: "'Montserrat', sans-serif",
            fontStyle: 'normal',
            fontWeight: 600,
            fontSize: '12px',
            lineHeight: '12px',
            letterSpacing: '0.5px',
        }),

        singleValue: (base, state) => ({
            ...base,
            padding: 0,
            fontFamily: "'Montserrat', sans-serif",
            fontStyle: 'normal',
            fontWeight: 600,
            fontSize: '12px',
            lineHeight: '12px',
            letterSpacing: '0.5px',
            color: state.isDisabled ? 'rgba(48, 65, 86, 0.5)' : '#304156',
        }),
        placeholder: base => ({
            ...base,
            fontFamily: "'Montserrat', sans-serif",
            fontStyle: 'normal',
            fontWeight: 400,
            fontSize: '12px',
            lineHeight: '12px',
            letterSpacing: '0.5px',
            color: '#304156',
        }),
        option: (provided, state) => ({
            ...provided,
            cursor: "pointer",
            textAlign: 'left',
        }),
        dropdownIndicator: base => ({
            ...base,
            padding: '0px 14px 0px 14px',
        }),
        indicatorSeparator: base => ({
            ...base,
            display: "none"
        }),
        valueContainer: base => ({
            ...base,
        })
    };
}



